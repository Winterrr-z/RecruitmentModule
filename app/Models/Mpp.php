<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Mpp extends Model
{
    use HasFactory;

    protected $table = 'mpps';

    protected $fillable = [
        'plan_name',
        'department',
        'job_title',
        'quota',
        'estimated_salary_min',
        'estimated_salary_max',
        'sla_days',
        'absolute_target_date',
        'status',
        'note',
        'last_activity_at',
    ];

    protected $casts = [
        'absolute_target_date' => 'date',
        'quota' => 'integer',
        'estimated_salary_min' => 'integer',
        'estimated_salary_max' => 'integer',
        'sla_days' => 'integer',
        'last_activity_at' => 'datetime',
        'status' => \App\Enums\MppStatus::class,
    ];

    public function rrs(): HasMany
    {
        return $this->hasMany(Rr::class, 'mpp_id');
    }

    public function vacancies(): HasManyThrough
    {
        return $this->hasManyThrough(
            Vacancy::class,
            Rr::class,
            'mpp_id',
            'rr_id',
            'id',
            'id'
        );
    }

    public function totalHired(): int
    {
        return Candidate::whereHas('vacancy.rr', function ($q) {
            $q->where('mpp_id', $this->id);
        })->where('status', \App\Enums\CandidateStatus::HIRED)->count();
    }

    public function sisaKuota(): int
    {
        return max(0, $this->quota - $this->totalHired());
    }

    public function isFilled(): bool
    {
        return $this->totalHired() >= $this->quota;
    }

    public function hasActiveCandidates(): bool
    {
        $vacancyIds = Vacancy::whereHas('rr', function ($q) {
            $q->where('mpp_id', $this->id);
        })->pluck('id');

        return Candidate::whereIn('vacancy_id', $vacancyIds)
            ->whereNotIn('status', [\App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::WITHDRAWN])
            ->exists();
    }

    public function hasPublishedRr(): bool
    {
        return $this->rrs()->where('status', 'Published')->exists();
    }

    public function getLastActivityDate(): Carbon
    {
        $dates = collect();
        $dates->push($this->last_activity_at ?? $this->updated_at);

        $latestRr = $this->rrs()->max('created_at');
        if ($latestRr) {
            $dates->push(Carbon::parse($latestRr));
        }

        $vacancyIds = Vacancy::whereHas('rr', function ($q) {
            $q->where('mpp_id', $this->id);
        })->pluck('id');

        if ($vacancyIds->isNotEmpty()) {
            $latestCandidate = Candidate::whereIn('vacancy_id', $vacancyIds)->max('created_at');
            if ($latestCandidate) {
                $dates->push(Carbon::parse($latestCandidate));
            }

            $candidateIds = Candidate::whereIn('vacancy_id', $vacancyIds)->pluck('id');
            if ($candidateIds->isNotEmpty()) {
                $latestMovement = CandidateMovement::whereIn('candidate_id', $candidateIds)->max('moved_at');
                if ($latestMovement) {
                    $dates->push(Carbon::parse($latestMovement));
                }
            }
        }

        return $dates->filter()->max() ?? $this->updated_at;
    }

    public function getComputedStatus(): string
    {
        if ($this->status === \App\Enums\MppStatus::CLOSED) {
            return 'Closed';
        }

        if ($this->isFilled()) {
            return 'Completed';
        }

        $now = now();
        $created = Carbon::parse($this->created_at);
        $target = Carbon::parse($this->absolute_target_date);
        $totalDays = max(1, $created->diffInDays($target));
        $elapsedDays = $created->diffInDays($now);
        $percent = ($elapsedDays / $totalDays) * 100;
        $daysRemaining = $now->diffInDays($target, false);

        $lastActivity = $this->getLastActivityDate();
        $daysSinceActivity = $lastActivity->diffInDays($now);

        if ($percent > 100 || $daysSinceActivity >= 14) {
            return 'Critical';
        }

        if ($percent >= 90 && $daysRemaining < 7) {
            return 'Urgent';
        }

        if ($daysSinceActivity >= 7) {
            return 'Need Attention';
        }
        
        if ($percent >= 51 && $percent <= 89 && $daysRemaining <= 30) {
            return 'Need Attention';
        }

        return 'In Progress';
    }

    public function getStatusBadge(): array
    {
        $status = $this->getComputedStatus();

        return match ($status) {
            'In Progress' => [
                'label' => 'In Progress',
                'color' => 'text-blue-700',
                'bg' => 'bg-blue-100',
                'dotColor' => 'bg-blue-500',
                'icon' => 'sync',
            ],
            'Need Attention' => [
                'label' => 'Need Attention',
                'color' => 'text-yellow-800',
                'bg' => 'bg-yellow-100',
                'dotColor' => 'bg-yellow-500',
                'icon' => 'warning',
            ],
            'Urgent' => [
                'label' => 'Urgent',
                'color' => 'text-orange-800',
                'bg' => 'bg-orange-100',
                'dotColor' => 'bg-orange-500',
                'icon' => 'priority_high',
            ],
            'Critical' => [
                'label' => 'Critical',
                'color' => 'text-red-800',
                'bg' => 'bg-red-100',
                'dotColor' => 'bg-red-500',
                'icon' => 'error',
            ],
            'Closed' => [
                'label' => 'Closed',
                'color' => 'text-gray-700',
                'bg' => 'bg-gray-200',
                'dotColor' => 'bg-gray-500',
                'icon' => 'lock',
            ],
            'Completed' => [
                'label' => 'Completed',
                'color' => 'text-green-800',
                'bg' => 'bg-green-100',
                'dotColor' => 'bg-green-500',
                'icon' => 'check_circle',
            ],
            default => [
                'label' => 'Unknown',
                'color' => 'text-gray-600',
                'bg' => 'bg-gray-100',
                'dotColor' => 'bg-gray-400',
                'icon' => 'help',
            ],
        };
    }
}
