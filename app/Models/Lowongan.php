<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongans';

    protected $fillable = [
        'recruitment_request_id',
        'quota',
        'job_title',
        'department',
        'employment_type',
        'location',
        'application_deadline',
        'show_salary',
        'estimated_salary_min',
        'estimated_salary_max',
        'job_description',
        'job_requirements',
        'status',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'show_salary' => 'boolean',
        'estimated_salary_min' => 'integer',
        'estimated_salary_max' => 'integer',
        'quota' => 'integer',
        'status' => \App\Enums\LowonganStatus::class,
    ];

    public function recruitmentRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'lowongan_id');
    }
}
