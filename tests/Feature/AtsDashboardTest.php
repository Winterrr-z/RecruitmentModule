<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\Candidate;
use App\Models\Scorecard;
use App\Models\InterviewSchedule;
use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $job1;
    private $job2;
    private $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        // Default stages seeded by migration are 1 (Applied) and 2 (Final)
        // Let's create a custom intermediate stage
        Stage::create([
            'nama' => 'HR Interview',
            'deskripsi' => 'HR Stage',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 2,
        ]);

        Stage::where('id', 2)->update(['urutan' => 3]);

        $mpp1 = \App\Models\Mpp::create([
            'nama_plan' => 'Plan IT',
            'departemen' => 'IT',
            'jabatan' => 'Software Engineer',
            'jumlah_kebutuhan' => 5,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $mpp2 = \App\Models\Mpp::create([
            'nama_plan' => 'Plan HR',
            'departemen' => 'HR',
            'jabatan' => 'HR Manager',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr1 = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp1->id,
            'jabatan' => 'Software Engineer',
            'departemen' => 'IT',
            'status' => 'Published',
            'deskripsi_pekerjaan' => 'Job description',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 5,
        ]);

        $this->job1 = Lowongan::create([
            'recruitment_request_id' => $rr1->id,
            'jabatan' => 'Software Engineer',
            'departemen' => 'IT',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Job description',
            'spesifikasi_kebutuhan' => 'Job requirements',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 5,
        ]);

        $rr2 = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp2->id,
            'jabatan' => 'HR Manager',
            'departemen' => 'HR',
            'status' => 'Published',
            'deskripsi_pekerjaan' => 'Job description',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 1,
        ]);

        $this->job2 = Lowongan::create([
            'recruitment_request_id' => $rr2->id,
            'jabatan' => 'HR Manager',
            'departemen' => 'HR',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Job description',
            'spesifikasi_kebutuhan' => 'Job requirements',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 1,
        ]);

        $this->candidate = Candidate::create([
            'lowongan_id' => $this->job1->id,
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'telepon' => '081234567890',
            'current_stage_id' => 1, // Applied
            'status' => 'Applied',
        ]);
    }

    public function test_dashboard_requires_auth()
    {
        $this->get(route('ats.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_can_access_dashboard_page()
    {
        $this->actingAs($this->user)
            ->get(route('ats.dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Ats\AtsDashboard::class);
    }

    public function test_can_filter_by_stage_and_job()
    {
        // Another candidate at a different stage and job
        $candidate2 = Candidate::create([
            'lowongan_id' => $this->job2->id,
            'nama' => 'Jane Smith',
            'email' => 'jane@example.com',
            'telepon' => '081234567891',
            'current_stage_id' => 2, // Final
            'status' => 'Applied',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            // By default selected stage is 1 (Applied)
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            // Switch stage to 2 (Final)
            ->set('selectedStageId', 2)
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe')
            // Filter by job 1
            ->set('selectedLowonganId', $this->job1->id)
            ->assertDontSee('Jane Smith')
            // Filter search
            ->set('search', 'Jane')
            ->assertDontSee('John Doe');
    }

    public function test_can_reject_candidate()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->call('reject', $this->candidate->id)
            ->assertSee("Kandidat 'John Doe' berhasil ditolak.");

        $this->assertEquals('Rejected', $this->candidate->fresh()->status);
    }

    public function test_can_blacklist_candidate()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->call('confirmBlacklist', $this->candidate->id)
            ->assertSet('showBlacklistModal', true)
            ->set('blacklistAlasan', 'Melanggar kode etik')
            ->call('blacklist')
            ->assertSet('showBlacklistModal', false)
            ->assertSee("Kandidat 'John Doe' berhasil dimasukkan ke daftar hitam (blacklist).");

        $this->assertEquals('Blacklisted', $this->candidate->fresh()->status);
        $this->assertDatabaseHas('blacklist', [
            'nama' => 'John Doe',
            'email' => 'john@example.com',
            'alasan' => 'Melanggar kode etik',
        ]);
    }

    public function test_validates_stage_requirements_on_move()
    {
        // Update stage 1 to require scorecard
        Stage::find(1)->update(['butuh_scorecard' => true]);

        // Attempt move without scorecard -> should fail validation
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->call('moveCandidate', $this->candidate->id, 2)
            ->assertSee("Kandidat 'John Doe' tidak dapat dipindahkan karena tahap saat ini ('Applied') membutuhkan scorecard yang belum diisi.");

        $this->assertEquals(1, $this->candidate->fresh()->current_stage_id);

        // Add scorecard
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'kriteria' => 'Keahlian Teknis',
            'bobot' => 100,
            'nilai' => 5,
        ]);

        // Attempt move again -> should succeed
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->call('moveCandidate', $this->candidate->id, 2)
            ->assertSee("Kandidat 'John Doe' berhasil dipindahkan ke stage 'Final'.");

        $this->assertEquals(2, $this->candidate->fresh()->current_stage_id);
    }

    public function test_can_approve_and_advance_candidate_to_next_stage()
    {
        // Check current stage is 1 (Applied)
        $this->assertEquals(1, $this->candidate->current_stage_id);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->call('approve', $this->candidate->id)
            ->assertSee("Kandidat 'John Doe' berhasil di-hire dan dipindahkan ke stage Final dengan status Offered.");

        $this->assertEquals(2, $this->candidate->fresh()->current_stage_id);
        $this->assertEquals('Offered', $this->candidate->fresh()->status);
    }

    public function test_dashboard_displays_manual_candidate_button()
    {
        // When no lowongan is selected, it shows the alert button but not the link
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->assertSee('Input Kandidat')
            ->assertDontSee(route('ats.candidate.manual', $this->job1->id));

        // When lowongan is selected, it shows the direct link
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class, ['selectedLowonganId' => $this->job1->id])
            ->assertSee(route('ats.candidate.manual', $this->job1->id));
    }

    public function test_dashboard_displays_candidate_detail_link()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsDashboard::class)
            ->assertSee(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));
    }
}
