<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\Mpp;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardIndexTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $mpp;
    private $lowongan;
    private $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup default stages
        Stage::firstOrCreate(['id' => 1], ['name' => 'Applied', 'description' => 'Default applied stage', 'sequence' => 1]);
        Stage::firstOrCreate(['id' => 2], ['name' => 'Final', 'description' => 'Default final stage', 'sequence' => 2]);

        $this->user = User::factory()->create(['role' => 'hr']);

        $this->mpp = Mpp::create([
            'plan_name' => 'Plan Admin',
            'department' => 'General',
            'job_title' => 'Admin Officer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Approved',
        ]);

        $rr_temp = \App\Models\RecruitmentRequest::create([
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
            'recruitment_request_id' => \App\Models\RecruitmentRequest::latest('id')->first()->id,
            'job_title' => 'Admin Officer',
            'department' => 'General',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job desc',
            'job_requirements' => 'Requirements',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $this->candidate = Candidate::create([
            'lowongan_id' => $this->lowongan->id,
            'name' => 'New Guy',
            'email' => 'newguy@example.com',
            'phone' => '081234567890',
            'current_stage_id' => 1, // Applied
            'status' => 'Applied',
        ]);
    }

    public function test_dashboard_page_requires_auth()
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_can_access_dashboard_page()
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\DashboardIndex::class);
    }

    public function test_dashboard_displays_correct_metrics()
    {
        // Add interview schedule for today
        InterviewSchedule::create([
            'candidate_id' => $this->candidate->id,
            'stage_id' => 1,
            'date' => now()->toDateString(),
            'time' => '10:00',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DashboardIndex::class)
            ->assertSet('activeLowonganCount', 1)
            ->assertSet('newCandidateCount', 1)
            ->assertSet('todayInterviewCount', 1)
            ->assertSee('Admin Officer')
            ->assertSee('General');
    }

    public function test_dashboard_carousel_navigation()
    {
        // Add a second active lowongan
        $rr_temp = \App\Models\RecruitmentRequest::create([
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
        $lowongan2 = Lowongan::create([
            'recruitment_request_id' => \App\Models\RecruitmentRequest::latest('id')->first()->id,
            'job_title' => 'Security Guard',
            'department' => 'General',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'job_description' => 'Job desc',
            'job_requirements' => 'Requirements',
            'employment_type' => 'contract',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DashboardIndex::class)
            ->assertSet('currentLowonganIndex', 0)
            ->call('nextLowongan')
            ->assertSet('currentLowonganIndex', 1)
            ->call('nextLowongan')
            ->assertSet('currentLowonganIndex', 0)
            ->call('previousLowongan')
            ->assertSet('currentLowonganIndex', 1);
    }

    public function test_dashboard_calendar_navigation()
    {
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\DashboardIndex::class)
            ->assertSet('currentMonth', $currentMonth)
            ->assertSet('currentYear', $currentYear);

        // Go to next month
        $component->call('changeMonth', 'next');
        
        $nextMonth = $currentMonth === 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth === 12 ? $currentYear + 1 : $currentYear;

        $component->assertSet('currentMonth', $nextMonth)
            ->assertSet('currentYear', $nextYear);

        // Go to previous month twice
        $component->call('changeMonth', 'prev')
            ->call('changeMonth', 'prev');

        $prevMonth = $currentMonth === 1 ? 12 : $currentMonth - 1;
        $prevYear = $currentMonth === 1 ? $currentYear - 1 : $currentYear;

        $component->assertSet('currentMonth', $prevMonth)
            ->assertSet('currentYear', $prevYear);
    }
}
