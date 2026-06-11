<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Candidate extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'candidates';

    protected $fillable = [
        'vacancy_id',
        'user_id',
        'name',
        'email',
        'phone',
        'cv_path',
        'portofolio_path',
        'current_stage_id',
        'status',
        'source',
        'offering_token',
        'offering_token_expires_at',
    ];

    protected $casts = [
        'offering_token_expires_at' => 'datetime',
        'status' => \App\Enums\CandidateStatus::class,
        'vacancy_id' => 'integer',
        'user_id' => 'integer',
        'current_stage_id' => 'integer',
    ];

    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id');
    }

    protected static function booted()
    {
        static::saved(function ($candidate) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_stage_counts');
        });

        static::deleted(function ($candidate) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_stage_counts');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    public function candidateMovements(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'candidate_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'candidate_id');
    }

    public function interviewSchedules(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class, 'candidate_id');
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class, 'candidate_id');
    }
}
