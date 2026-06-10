<?php

namespace Tests\Unit;

use App\Models\Candidate;
use App\Models\Vacancy;
use App\Models\User;
use App\Models\Stage;
use PHPUnit\Framework\TestCase;

class CandidateTest extends TestCase
{
    /**
     * Test that Candidate model has correct fillable properties.
     */
    public function test_candidate_has_fillable_properties(): void
    {
        $candidate = new Candidate();
        
        $this->assertEquals([
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
        ], $candidate->getFillable());
    }
}
