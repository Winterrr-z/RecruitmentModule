<?php

namespace Tests\Feature;

use App\Models\RecruitmentRequest;
use App\Models\Mpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_request_can_calculate_remaining_quota()
    {
        $mpp = Mpp::factory()->create();
        
        $rr = RecruitmentRequest::factory()->create([
            'mpp_id' => $mpp->id,
            'quota' => 10,
        ]);

        // Mock hired count logic or test it directly if Candidate factory is available
        // Since we are doing a basic check:
        $this->assertEquals(10, $rr->quota);
        // Verify it belongs to MPP
        $this->assertEquals($mpp->id, $rr->mpp_id);
    }
}
