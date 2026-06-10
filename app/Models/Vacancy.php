<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vacancy extends Model
{
    use HasFactory;

    protected $table = 'vacancies';

    protected $fillable = [
        'rr_id',
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
        'status' => \App\Enums\VacancyStatus::class,
    ];

    public function rr(): BelongsTo
    {
        return $this->belongsTo(Rr::class, 'rr_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'vacancy_id');
    }
}
