<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AtsManualAndBlacklistTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        $mpp = \App\Models\Mpp::create([
            'plan_name' => 'Staff Plan',
            'department' => 'Sales',
            'job_title' => 'Sales Staff',
            'quota' => 2,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $this->job = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Sales Staff',
            'department' => 'Sales',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 2,
        ]);
    }

    public function test_manual_candidate_creation_saves_to_db()
    {
        Storage::fake('local');
        $dummyCv = UploadedFile::fake()->create('my_cv.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsManualCandidate::class, ['vacancyId' => $this->job->id])
            ->set('name', 'Alice Johnson')
            ->set('email', 'alice@example.com')
            ->set('phone', '0899999999')
            ->set('cv', $dummyCv)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.dashboard', ['selectedVacancyId' => $this->job->id]));

        $this->assertDatabaseHas('candidates', [
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'source' => 'manual',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);

        $candidate = Candidate::where('email', 'alice@example.com')->first();
        $this->assertNotNull($candidate->cv_path);
        Storage::disk('local')->assertExists($candidate->cv_path);
    }

    public function test_blacklist_manages_records_and_picker()
    {
        // 1. Create a candidate to test the picker
        $cand = Candidate::create([
            'vacancy_id' => $this->job->id,
            'name' => 'Bad Guy',
            'email' => 'bad@example.com',
            'phone' => '0866666666',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);

        // Create an inactive candidate to test blacklisting inactive records
        $inactiveCand = Candidate::create([
            'vacancy_id' => $this->job->id,
            'name' => 'Bad Guy Inactive',
            'email' => 'bad@example.com',
            'phone' => '0866666666',
            'current_stage_id' => 2,
            'status' => 'Withdrawn',
        ]);

        // 2. Test Livewire component Blacklist
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsBlacklist::class)
            ->set('search', '')
            // Trigger picker search
            ->set('candidateSearch', 'Bad')
            ->call('selectCandidate', $cand->id)
            ->assertSet('name', 'Bad Guy')
            ->assertSet('email', 'bad@example.com')
            ->assertSet('phone', '0866666666')
            ->set('reason', 'Fraud detected')
            ->call('save')
            ->assertHasNoErrors();

        // Check blacklist row added
        $this->assertDatabaseHas('blacklist', [
            'name' => 'Bad Guy',
            'email' => 'bad@example.com',
            'reason' => 'Fraud detected',
        ]);

        // Check active candidate directly becomes REJECTED
        $this->assertEquals(\App\Enums\CandidateStatus::REJECTED, $cand->fresh()->status);

        // Check inactive candidate becomes BLACKLISTED
        $this->assertEquals(\App\Enums\CandidateStatus::BLACKLISTED, $inactiveCand->fresh()->status);

        // 3. Test blacklist delete
        $blacklistRow = Blacklist::where('email', 'bad@example.com')->first();
        
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsBlacklist::class)
            ->call('deleteBlacklist', $blacklistRow->id);

        $this->assertDatabaseMissing('blacklist', [
            'email' => 'bad@example.com',
        ]);

        // Active candidate remains REJECTED (inactive only)
        $this->assertEquals(\App\Enums\CandidateStatus::REJECTED, $cand->fresh()->status);

        // Inactive candidate that was BLACKLISTED changes to REJECTED (inactive only)
        $this->assertEquals(\App\Enums\CandidateStatus::REJECTED, $inactiveCand->fresh()->status);
    }

    public function test_can_create_manual_candidate_without_vacancy()
    {
        Storage::fake('local');
        $dummyCv = UploadedFile::fake()->create('my_cv.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsManualCandidate::class)
            ->set('name', 'Independent Candidate')
            ->set('email', 'independent@example.com')
            ->set('phone', '0899999998')
            ->set('cv', $dummyCv)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.dashboard'));

        $this->assertDatabaseHas('candidates', [
            'name' => 'Independent Candidate',
            'email' => 'independent@example.com',
            'vacancy_id' => null,
            'source' => 'manual',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);

        $candidate = Candidate::where('email', 'independent@example.com')->first();
        $this->assertNotNull($candidate->cv_path);
        Storage::disk('local')->assertExists($candidate->cv_path);
    }

    public function test_can_select_vacancy_from_dropdown_and_save()
    {
        Storage::fake('local');
        $dummyCv = UploadedFile::fake()->create('my_cv.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsManualCandidate::class)
            ->set('name', 'Changed Vacancy Candidate')
            ->set('email', 'changed@example.com')
            ->set('phone', '0899999997')
            ->set('cv', $dummyCv)
            ->set('vacancyId', $this->job->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.dashboard', ['selectedVacancyId' => $this->job->id]));

        $this->assertDatabaseHas('candidates', [
            'name' => 'Changed Vacancy Candidate',
            'email' => 'changed@example.com',
            'vacancy_id' => $this->job->id,
            'source' => 'manual',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);
    }
}
