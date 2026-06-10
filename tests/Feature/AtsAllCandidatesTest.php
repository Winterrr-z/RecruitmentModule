<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsAllCandidatesTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $job1;
    private $job2;
    private $candidate1;
    private $candidate2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        $mpp1 = \App\Models\Mpp::create([
            'plan_name' => 'Plan IT',
            'department' => 'IT',
            'job_title' => 'Software Engineer',
            'quota' => 5,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $mpp2 = \App\Models\Mpp::create([
            'plan_name' => 'Plan HR',
            'department' => 'HR',
            'job_title' => 'HR Manager',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr1 = \App\Models\Rr::create([
            'mpp_id' => $mpp1->id,
            'job_title' => 'Software Engineer',
            'department' => 'IT',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 5,
        ]);

        $this->job1 = Vacancy::create([
            'rr_id' => $rr1->id,
            'mpp_id' => $mpp1->id,
            'job_title' => 'Software Engineer',
            'department' => 'IT',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 5,
        ]);

        $rr2 = \App\Models\Rr::create([
            'mpp_id' => $mpp2->id,
            'job_title' => 'HR Manager',
            'department' => 'HR',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $this->job2 = Vacancy::create([
            'rr_id' => $rr2->id,
            'mpp_id' => $mpp2->id,
            'job_title' => 'HR Manager',
            'department' => 'HR',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job description',
            'job_requirements' => 'Job requirements',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        // Stage 1: Applied, Stage 2: Final
        $this->candidate1 = Candidate::create([
            'vacancy_id' => $this->job1->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);

        $this->candidate2 = Candidate::create([
            'vacancy_id' => $this->job2->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '081234567891',
            'current_stage_id' => 2,
            'status' => 'Hired',
        ]);
    }

    public function test_all_candidates_page_requires_auth()
    {
        $this->get(route('ats.candidates'))
            ->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_access_all_candidates_page()
    {
        $this->actingAs($this->user)
            ->get(route('ats.candidates'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Ats\AtsAllCandidates::class);
    }

    public function test_can_search_candidates_by_name_and_email()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('search', 'jane@example.com')
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe');
    }

    public function test_can_filter_candidates_by_vacancy()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->set('filterVacancy', $this->job1->id)
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('filterVacancy', $this->job2->id)
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe');
    }

    public function test_can_filter_candidates_by_status()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->set('filterStatus', 'Applied')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('filterStatus', 'Hired')
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe');
    }

    public function test_can_filter_candidates_by_stage()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->set('filterStage', 1)
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->set('filterStage', 2)
            ->assertSee('Jane Smith')
            ->assertDontSee('John Doe');
    }

    public function test_renders_detail_profile_link()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee(route('ats.candidate.detail', ['candidateId' => $this->candidate1->id]))
            ->assertSee(route('ats.candidate.detail', ['candidateId' => $this->candidate2->id]));
    }

    public function test_renders_blacklist_link()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsAllCandidates::class)
            ->assertSee(route('ats.blacklist'));
    }
}
