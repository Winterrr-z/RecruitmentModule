<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    protected $table = 'stages';

    protected static function booted()
    {
        static::saved(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('stages_all');
        });

        static::deleted(function ($model) {
            \Illuminate\Support\Facades\Cache::forget('stages_all');
        });
    }

    public static function getAllCached()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('stages_all', function () {
            return self::orderBy('sequence', 'asc')->get();
        });
    }

    protected $fillable = [
        'name',
        'description',
        'needs_scorecard',
        'needs_schedule',
        'sequence',
        'scorecard_criteria',
        'interview_type',
        'default_location',
        'default_virtual_link',
    ];

    protected $casts = [
        'needs_scorecard' => 'boolean',
        'needs_schedule' => 'boolean',
        'sequence' => 'integer',
        'scorecard_criteria' => 'array',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'current_stage_id');
    }

    public function candidateMovementsFrom(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'from_stage_id');
    }

    public function candidateMovementsTo(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'to_stage_id');
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class, 'stage_id');
    }

    public function interviewSchedules(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class, 'stage_id');
    }
}
