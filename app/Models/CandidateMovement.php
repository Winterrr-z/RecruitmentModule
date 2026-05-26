<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateMovement extends Model
{
    protected $table = 'candidate_movements';

    protected $fillable = [
        'candidate_id',
        'from_stage_id',
        'to_stage_id',
        'moved_at',
        'interviewer_notes',
    ];

    protected $casts = [
        'moved_at' => 'datetime',
        'candidate_id' => 'integer',
        'from_stage_id' => 'integer',
        'to_stage_id' => 'integer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }
}
