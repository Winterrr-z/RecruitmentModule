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
        'nama_plan',
        'departemen',
        'jabatan',
        'jumlah_kebutuhan',
        'estimasi_gaji_min',
        'estimasi_gaji_max',
        'sla_hari',
        'target_waktu_absolut',
        'status',
        'note',
        'last_activity_at',
    ];

    protected $casts = [
        'target_waktu_absolut' => 'date',
        'jumlah_kebutuhan' => 'integer',
        'estimasi_gaji_min' => 'integer',
        'estimasi_gaji_max' => 'integer',
        'sla_hari' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    public function recruitmentRequests(): HasMany
    {
        return $this->hasMany(RecruitmentRequest::class, 'mpp_id');
    }

    public function lowongans(): HasManyThrough
    {
        return $this->hasManyThrough(
            Lowongan::class,
            RecruitmentRequest::class,
            'mpp_id',
            'recruitment_request_id',
            'id',
            'id'
        );
    }

    public function totalHired(): int
    {
        return Candidate::whereHas('lowongan.recruitmentRequest', function ($q) {
            $q->where('mpp_id', $this->id);
        })->where('status', 'Hired')->count();
    }

    public function sisaKuota(): int
    {
        return max(0, $this->jumlah_kebutuhan - $this->totalHired());
    }

    public function isFilled(): bool
    {
        return $this->totalHired() >= $this->jumlah_kebutuhan;
    }

    public function hasActiveCandidates(): bool
    {
        $lowonganIds = Lowongan::whereHas('recruitmentRequest', function ($q) {
            $q->where('mpp_id', $this->id);
        })->pluck('id');

        return Candidate::whereIn('lowongan_id', $lowonganIds)
            ->whereNotIn('status', ['Rejected', 'Hired', 'Withdrawn'])
            ->exists();
    }

    public function hasPublishedRr(): bool
    {
        return $this->recruitmentRequests()->where('status', 'Published')->exists();
    }

    public function getLastActivityDate(): Carbon
    {
        $dates = collect();
        $dates->push($this->last_activity_at ?? $this->updated_at);

        $latestRr = $this->recruitmentRequests()->max('created_at');
        if ($latestRr) {
            $dates->push(Carbon::parse($latestRr));
        }

        $lowonganIds = Lowongan::whereHas('recruitmentRequest', function ($q) {
            $q->where('mpp_id', $this->id);
        })->pluck('id');

        if ($lowonganIds->isNotEmpty()) {
            $latestCandidate = Candidate::whereIn('lowongan_id', $lowonganIds)->max('created_at');
            if ($latestCandidate) {
                $dates->push(Carbon::parse($latestCandidate));
            }

            $candidateIds = Candidate::whereIn('lowongan_id', $lowonganIds)->pluck('id');
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
        if (strtolower($this->status) === 'closed') {
            return 'Closed';
        }

        if ($this->isFilled()) {
            return 'Completed';
        }

        $now = now();
        $created = Carbon::parse($this->created_at);
        $target = Carbon::parse($this->target_waktu_absolut);
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
