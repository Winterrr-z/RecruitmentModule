<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RecruitmentRequest extends Model
{
    use HasFactory;

    protected $table = 'recruitment_requests';

    protected $fillable = [
        'mpp_id',
        'job_title',
        'department',
        'estimated_salary_min',
        'estimated_salary_max',
        'expected_join_date',
        'job_description',
        'job_requirements',
        'employment_type',
        'location',
        'application_deadline',
        'show_salary',
        'status',
        'quota',
    ];

    protected $casts = [
        'expected_join_date' => 'date',
        'application_deadline' => 'date',
        'show_salary' => 'boolean',
        'estimated_salary_min' => 'integer',
        'estimated_salary_max' => 'integer',
        'quota' => 'integer',
        'status' => \App\Enums\RrStatus::class,
    ];

    public function mpp(): BelongsTo
    {
        return $this->belongsTo(Mpp::class, 'mpp_id');
    }

    public function lowongan(): HasOne
    {
        return $this->hasOne(Lowongan::class, 'recruitment_request_id');
    }

    public function candidates(): HasManyThrough
    {
        return $this->hasManyThrough(
            Candidate::class,
            Lowongan::class,
            'recruitment_request_id',
            'lowongan_id',
            'id',
            'id'
        );
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['Completed/Closed']);
    }

    public function hiredCount(): int
    {
        return $this->lowongan?->candidates()->where('status', \App\Enums\CandidateStatus::HIRED)->count() ?? 0;
    }
}
