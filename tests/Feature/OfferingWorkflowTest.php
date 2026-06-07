<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\Mpp;
use App\Models\Candidate;
use App\Mail\OfferingLetterMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class OfferingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $mpp;
    private $lowongan;
    private $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default stages exist (Applied: 1, Final: 2)
        Stage::firstOrCreate(['id' => 1], ['name' => 'Applied', 'description' => 'Default applied stage', 'sequence' => 1]);
        Stage::firstOrCreate(['id' => 2], ['name' => 'Final', 'description' => 'Default final stage', 'sequence' => 2]);

        $this->user = User::factory()->create(['role' => 'hr']);

        $this->mpp = Mpp::create([
            'plan_name' => 'Plan IT Support',
            'department' => 'IT',
            'job_title' => 'IT Support',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Approved',
        ]);

        $rr = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $this->lowongan = Lowongan::create([
            'recruitment_request_id' => $rr->id,
            'job_title' => 'IT Support',
            'department' => 'IT',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $this->candidate = Candidate::create([
            'lowongan_id' => $this->lowongan->id,
            'name' => 'Candidate Tester',
            'email' => 'tester@example.com',
            'phone' => '081234567890',
            'current_stage_id' => 1, // Applied
            'status' => 'Applied',
        ]);
    }

    public function test_offering_send_requires_auth()
    {
        $this->get(route('ats.offering.send', ['candidateId' => $this->candidate->id]))
            ->assertRedirect(route('login'));
    }

    public function test_offering_send_validates_stage_and_quota()
    {
        // 1. Candidate is in 'Applied' stage, not 'Final'
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\OfferingSend::class, ['candidateId' => $this->candidate->id])
            ->assertSet('isValid', false)
            ->assertSet('errorMessage', 'Kandidat harus berada di stage "Final" untuk dikirimi offering letter.');

        // Move candidate to 'Final' (id 2)
        $this->candidate->update(['current_stage_id' => 2]);

        // 2. Candidate is now valid
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\OfferingSend::class, ['candidateId' => $this->candidate->id])
            ->assertSet('isValid', true);

        // 3. Set lowongan quota to 0
        $this->lowongan->update(['quota' => 0]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\OfferingSend::class, ['candidateId' => $this->candidate->id])
            ->assertSet('isValid', false)
            ->assertSet('errorMessage', 'Kuota lowongan untuk jabatan "' . $this->lowongan->job_title . '" sudah habis.');
    }

    public function test_can_send_offering_letter()
    {
        Mail::fake();

        $this->candidate->update(['current_stage_id' => 2]); // Final

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\OfferingSend::class, ['candidateId' => $this->candidate->id])
            ->assertSet('isValid', true)
            ->call('sendOffering')
            ->assertRedirect(route('ats.dashboard'));

        $this->candidate = $this->candidate->fresh();
        $this->assertEquals(\App\Enums\CandidateStatus::OFFERED, $this->candidate->status);
        $this->assertNotNull($this->candidate->offering_token);
        $this->assertNotNull($this->candidate->offering_token_expires_at);
        $this->assertTrue($this->candidate->offering_token_expires_at->isAfter(now()));

        Mail::assertSent(OfferingLetterMail::class, function ($mail) {
            return $mail->hasTo($this->candidate->email) &&
                   $mail->candidate->id === $this->candidate->id &&
                   $mail->lowongan->id === $this->lowongan->id;
        });
    }

    public function test_offering_response_invalid_token()
    {
        // View with non-existent token
        Livewire::test(\App\Livewire\OfferingResponse::class, ['token' => 'invalid-token-here'])
            ->assertSet('statusResponse', 'invalid')
            ->assertSee('Tautan Tidak Valid');
    }

    public function test_offering_response_expired_token()
    {
        // Create candidate with expired token
        $this->candidate->update([
            'status' => 'Applied',
            'offering_token' => 'expired-token',
            'offering_token_expires_at' => now()->subHour(),
        ]);

        Livewire::test(\App\Livewire\OfferingResponse::class, ['token' => 'expired-token'])
            ->assertSet('statusResponse', 'expired')
            ->assertSee('Tawaran Sudah Kedaluwarsa');

        $this->assertEquals(\App\Enums\CandidateStatus::EXPIRED, $this->candidate->fresh()->status);
        $this->assertNull($this->candidate->fresh()->offering_token);
    }

    public function test_offering_response_can_accept_offering_livewire()
    {
        $this->candidate->update([
            'status' => 'Offered',
            'offering_token' => 'valid-token',
            'offering_token_expires_at' => now()->addDays(3),
        ]);

        Livewire::test(\App\Livewire\OfferingResponse::class, ['token' => 'valid-token'])
            ->assertSet('statusResponse', null)
            ->call('handleResponse', 'terima')
            ->assertSet('statusResponse', 'success_accept')
            ->assertSee('Selamat! Anda Telah Menerima Tawaran');

        $this->candidate = $this->candidate->fresh();
        $this->assertEquals(\App\Enums\CandidateStatus::HIRED, $this->candidate->status);
        $this->assertNull($this->candidate->offering_token);
        
        $this->lowongan = $this->lowongan->fresh();
        $this->assertEquals(0, $this->lowongan->quota);
        $this->assertEquals(\App\Enums\LowonganStatus::COMPLETED_CLOSED, $this->lowongan->status);

        $this->mpp = $this->mpp->fresh();
        $this->assertEquals(\App\Enums\MppStatus::COMPLETED_CLOSED, $this->mpp->status);
    }

    public function test_offering_response_can_reject_offering_livewire()
    {
        $this->candidate->update([
            'status' => 'Offered',
            'offering_token' => 'valid-token',
            'offering_token_expires_at' => now()->addDays(3),
        ]);

        Livewire::test(\App\Livewire\OfferingResponse::class, ['token' => 'valid-token'])
            ->assertSet('statusResponse', null)
            ->call('handleResponse', 'tolak')
            ->assertSet('statusResponse', 'success_reject')
            ->assertSee('Tawaran Telah Ditolak');

        $this->candidate = $this->candidate->fresh();
        $this->assertEquals(\App\Enums\CandidateStatus::DECLINED, $this->candidate->status);
        $this->assertNull($this->candidate->offering_token);
        
        $this->lowongan = $this->lowongan->fresh();
        $this->assertEquals(1, $this->lowongan->quota); // remains 1
    }

    public function test_offering_response_can_accept_offering_post_route()
    {
        $this->candidate->update([
            'status' => 'Offered',
            'offering_token' => 'valid-token',
            'offering_token_expires_at' => now()->addDays(3),
        ]);

        $this->post(route('offering.respond', ['token' => 'valid-token']), ['choice' => 'terima'])
            ->assertRedirect(route('offering.response', ['token' => 'valid-token']))
            ->assertSessionHas('status', 'terima');

        $this->candidate = $this->candidate->fresh();
        $this->assertEquals(\App\Enums\CandidateStatus::HIRED, $this->candidate->status);
        $this->assertNull($this->candidate->offering_token);
        
        $this->lowongan = $this->lowongan->fresh();
        $this->assertEquals(0, $this->lowongan->quota);
        $this->assertEquals(\App\Enums\LowonganStatus::COMPLETED_CLOSED, $this->lowongan->status);
    }

    public function test_offering_expire_cron_job()
    {
        // 1. Expired candidate
        $expiredCandidate = Candidate::create([
            'lowongan_id' => $this->lowongan->id,
            'name' => 'Expired Candidate',
            'email' => 'expired@example.com',
            'phone' => '081234567895',
            'current_stage_id' => 2,
            'status' => 'Offered',
            'offering_token' => 'expired-token-1',
            'offering_token_expires_at' => now()->subMinutes(10),
        ]);

        // 2. Active candidate
        $activeCandidate = Candidate::create([
            'lowongan_id' => $this->lowongan->id,
            'name' => 'Active Candidate',
            'email' => 'active@example.com',
            'phone' => '081234567896',
            'current_stage_id' => 2,
            'status' => 'Offered',
            'offering_token' => 'active-token-1',
            'offering_token_expires_at' => now()->addDays(2),
        ]);

        // Run the command
        Artisan::call('offerings:expire');

        $this->assertEquals(\App\Enums\CandidateStatus::EXPIRED, $expiredCandidate->fresh()->status);
        $this->assertNull($expiredCandidate->fresh()->offering_token);

        $this->assertEquals(\App\Enums\CandidateStatus::OFFERED, $activeCandidate->fresh()->status);
        $this->assertEquals('active-token-1', $activeCandidate->fresh()->offering_token);
    }
}
