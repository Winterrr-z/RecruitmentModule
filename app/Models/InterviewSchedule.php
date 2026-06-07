<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    protected $table = 'interview_schedules';

    protected $fillable = [
        'candidate_id',
        'stage_id',
        'date',
        'time',
        'venue',
        'virtual_link',
    ];

    protected $casts = [
        'date' => 'date',
        'candidate_id' => 'integer',
        'stage_id' => 'integer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }
}
