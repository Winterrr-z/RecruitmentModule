<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Mpp;
use App\Models\Candidate;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_dashboard()
    {
        $this->get(route('candidate.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_logged_in_candidate_can_access_dashboard()
    {
        $user = User::factory()->create(['role' => 'applicant']);

        $this->actingAs($user)
            ->get(route('candidate.dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Cw\CandidateDashboard::class);
    }

    public function test_dashboard_displays_correct_application_counts()
    {
        $user = User::factory()->create(['role' => 'applicant']);
        
        $mpp = Mpp::create([
            'plan_name' => 'Plan A',
            'department' => 'IT',
            'job_title' => 'Developer',
            'quota' => 1,
            'sla_days' => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
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
        $vacancy = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Developer',
            'department' => 'IT',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'job_description' => 'Kerja',
            'job_requirements' => 'Bisa ngoding',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        // Assumes Stage ID 1 (Applied) is seeded by migration
        $stageAppliedId = 1;

        $stageRejected = Stage::create([
            'name' => 'Rejected',
            'sequence' => 99,
        ]);

        // Active application
        Candidate::create([
            'vacancy_id' => $vacancy->id,
            'user_id' => $user->id,
            'name' => 'User',
            'email' => $user->email,
            'phone' => '0812345',
            'current_stage_id' => $stageAppliedId,
            'status' => 'Applied',
        ]);

        // History application
        Candidate::create([
            'vacancy_id' => $vacancy->id,
            'user_id' => $user->id,
            'name' => 'User',
            'email' => $user->email,
            'phone' => '0812345',
            'current_stage_id' => $stageRejected->id,
            'status' => 'Rejected',
        ]);

        $this->actingAs($user)
            ->get(route('candidate.dashboard'))
            ->assertSee('Developer')
            ->assertSee('Applied')
            ->assertSee('Tidak Lolos');
    }
}
