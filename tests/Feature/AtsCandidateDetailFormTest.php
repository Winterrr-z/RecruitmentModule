<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\Candidate;
use App\Models\Scorecard;
use App\Models\InterviewSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AtsCandidateDetailFormTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $job;
    private $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        $mpp = \App\Models\Mpp::create([
            'nama_plan' => 'IT Engineer Plan',
            'departemen' => 'IT',
            'jabatan' => 'IT Engineer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $this->job = Lowongan::create([
            'mpp_id' => $mpp->id,
            'jabatan' => 'IT Engineer',
            'departemen' => 'IT',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Job description',
            'spesifikasi_kebutuhan' => 'Job requirements',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 1,
        ]);

        $this->candidate = Candidate::create([
            'lowongan_id' => $this->job->id,
            'nama' => 'Bob Smith',
            'email' => 'bob@example.com',
            'telepon' => '081234567899',
            'current_stage_id' => 1, // Applied
            'status' => 'Applied',
            'cv_path' => 'cvs/bob_cv.pdf',
        ]);

        // Fake files
        Storage::fake('local');
        Storage::disk('local')->put('cvs/bob_cv.pdf', 'dummy content');
    }

    public function test_detail_page_displays_profile_data()
    {
        $this->actingAs($this->user)
            ->get(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]))
            ->assertSuccessful()
            ->assertSee('Bob Smith')
            ->assertSee('bob@example.com')
            ->assertSee('Download CV');
    }

    public function test_can_download_cv_stream()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsCandidateDetail::class, ['candidateId' => $this->candidate->id])
            ->call('downloadCv')
            ->assertFileDownloaded('bob_cv.pdf');
    }

    public function test_displays_scorecard_and_schedule_when_required()
    {
        // 1. By default, stage 1 (Applied) does not require scorecard or schedule
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsCandidateDetail::class, ['candidateId' => $this->candidate->id])
            ->assertDontSee('Scorecard Evaluasi')
            ->assertDontSee('Jadwal Interview');

        // 2. Change stage 1 requirements
        Stage::find(1)->update([
            'butuh_scorecard' => true,
            'butuh_jadwal' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsCandidateDetail::class, ['candidateId' => $this->candidate->id])
            ->assertSee('Scorecard Evaluasi')
            ->assertSee('Jadwal Interview');
    }

    public function test_schedule_form_saves_schedule()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->set('tanggal', '2026-06-15')
            ->set('waktu', '10:00')
            ->set('tempat', 'Room 302')
            ->set('tautan_virtual', 'https://meet.google.com/test')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        $this->assertDatabaseHas('interview_schedules', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'tanggal' => '2026-06-15 00:00:00',
            'waktu' => '10:00',
            'tempat' => 'Room 302',
            'tautan_virtual' => 'https://meet.google.com/test',
        ]);
    }

    public function test_schedule_form_populates_defaults_from_stage()
    {
        // Update stage with scheduling defaults
        Stage::find(1)->update([
            'tipe_wawancara' => 'hybrid',
            'lokasi_default' => 'Gedung A, Ruang 101',
            'tautan_virtual_default' => 'https://zoom.us/j/1234567890',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->assertSet('tempat', 'Gedung A, Ruang 101')
            ->assertSet('tautan_virtual', 'https://zoom.us/j/1234567890');
    }

    public function test_scorecard_form_validation_and_saving()
    {
        // Setup predefined criteria on Stage 1
        Stage::find(1)->update([
            'butuh_scorecard' => true,
            'scorecard_kriteria' => [
                ['kriteria' => 'Communication', 'bobot' => 40],
                ['kriteria' => 'Technical skill', 'bobot' => 60],
            ],
        ]);

        // 1. Validation error: scores must be between 1 and 10
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->set('kriteriaList.0.nilai', 15) // invalid score (> 10)
            ->call('save')
            ->assertHasErrors(['kriteriaList.0.nilai']);

        // 2. Successful save
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->set('kriteriaList.0.nilai', 8)
            ->set('kriteriaList.1.nilai', 9)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        $this->assertDatabaseHas('scorecards', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'kriteria' => 'Communication',
            'bobot' => 40,
            'nilai' => 8,
        ]);

        $this->assertDatabaseHas('scorecards', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'kriteria' => 'Technical skill',
            'bobot' => 60,
            'nilai' => 9,
        ]);
    }
}
