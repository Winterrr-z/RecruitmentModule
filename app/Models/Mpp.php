<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Casts\CurrencyCast;

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
        'estimated_salary_min' => CurrencyCast::class,
        'estimated_salary_max' => CurrencyCast::class,
        'sla_days' => 'integer',
        'last_activity_at' => 'datetime',
        'status' => \App\Enums\MppStatus::class,
    ];

    protected static function booted()
    {
        static::saved(function ($mpp) {
            \Illuminate\Support\Facades\Cache::forget('mpp_unique_departments');
        });

        static::deleted(function ($mpp) {
            \Illuminate\Support\Facades\Cache::forget('mpp_unique_departments');
        });
    }

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
        if (isset($this->hired_count)) {
            return (int) $this->hired_count;
        }

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

    public function hasActiveRr(): bool
    {
        return $this->rrs()->whereIn('status', ['Ready to Publish', 'Published'])->exists();
    }

    public function getLastActivityDate(): Carbon
    {
        if (isset($this->latest_rr_date) || isset($this->latest_candidate_date) || isset($this->latest_movement_date)) {
            $dates = collect([
                $this->last_activity_at ?? $this->updated_at,
                $this->latest_rr_date ? Carbon::parse($this->latest_rr_date) : null,
                $this->latest_candidate_date ? Carbon::parse($this->latest_candidate_date) : null,
                $this->latest_movement_date ? Carbon::parse($this->latest_movement_date) : null,
            ]);
            return $dates->filter()->max() ?? $this->updated_at;
        }

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

    public function getComputedStatus(): ?string
    {
        if (in_array($this->status, [\App\Enums\MppStatus::CLOSED, \App\Enums\MppStatus::COMPLETED])) {
            return $this->status->value;
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
}
