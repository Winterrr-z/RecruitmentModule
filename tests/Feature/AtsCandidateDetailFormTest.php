<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Vacancy;
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
            'plan_name' => 'IT Engineer Plan',
            'department' => 'IT',
            'job_title' => 'IT Engineer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr = \App\Models\Rr::create([
            'mpp_id' => $mpp->id,
            'job_title' => 'IT Engineer',
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

        $this->job = Vacancy::create([
            'rr_id' => $rr->id,
            'job_title' => 'IT Engineer',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $this->candidate = Candidate::create([
            'vacancy_id' => $this->job->id,
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'phone' => '081234567899',
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
            'needs_scorecard' => true,
            'needs_schedule' => true,
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
            ->set('date', '2026-06-15')
            ->set('time', '10:00')
            ->set('venue', 'Room 302')
            ->set('virtual_link', 'https://meet.google.com/test')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        $this->assertDatabaseHas('interview_schedules', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'date' => '2026-06-15 00:00:00',
            'time' => '10:00',
            'venue' => 'Room 302',
            'virtual_link' => 'https://meet.google.com/test',
        ]);
    }

    public function test_schedule_form_populates_defaults_from_stage()
    {
        // Update stage with scheduling defaults
        Stage::find(1)->update([
            'interview_type' => 'hybrid',
            'default_location' => 'Gedung A, Ruang 101',
            'default_virtual_link' => 'https://zoom.us/j/1234567890',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->assertSet('venue', 'Gedung A, Ruang 101')
            ->assertSet('virtual_link', 'https://zoom.us/j/1234567890');
    }

    public function test_scorecard_form_validation_and_saving()
    {
        // Setup predefined criteria on Stage 1
        Stage::find(1)->update([
            'needs_scorecard' => true,
            'scorecard_criteria' => [
                ['criteria' => 'Communication', 'weight' => 40],
                ['criteria' => 'Technical skill', 'weight' => 60],
            ],
        ]);

        // 1. Validation error: scores must be between 1 and 100
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->set('kriteriaList.0.score', 150) // invalid score (> 100)
            ->call('save')
            ->assertHasErrors(['kriteriaList.0.score']);

        // 2. Successful save
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId' => 1,
            ])
            ->set('kriteriaList.0.score', 8)
            ->set('kriteriaList.1.score', 9)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        $this->assertDatabaseHas('scorecards', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'criteria' => 'Communication',
            'weight' => 40,
            'score' => 8,
        ]);

        $this->assertDatabaseHas('scorecards', [
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'criteria' => 'Technical skill',
            'weight' => 60,
            'score' => 9,
        ]);
    }
}
