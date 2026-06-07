<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scorecard extends Model
{
    protected $table = 'scorecards';

    protected $fillable = [
        'candidate_id',
        'stage_id',
        'criteria',
        'weight',
        'score',
    ];

    protected $casts = [
        'weight' => 'integer',
        'score' => 'integer',
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
