<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class MppTest extends TestCase
{
    use RefreshDatabase;

    public function test_mpp_index_page_contains_livewire_component()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('mpp.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Mpp\MppIndex::class);
    }

    public function test_can_create_mpp()
    {
        Livewire::test(\App\Livewire\Mpp\MppForm::class)
            ->set('plan_name', 'Test Plan')
            ->set('department', 'IT')
            ->set('job_title', 'Software Engineer')
            ->set('quota', 2)
            ->set('estimated_salary_min', '10,000,000')
            ->set('estimated_salary_max', '15,000,000')
            ->set('sla_days', 90)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpps', [
            'plan_name' => 'Test Plan',
            'department' => 'IT',
            'job_title' => 'Software Engineer',
            'quota' => 2,
            'estimated_salary_min' => 10000000,
            'estimated_salary_max' => 15000000,
            'sla_days' => 90,
            'status' => \App\Enums\MppStatus::DRAFT,
        ]);
    }

    public function test_mpp_detail_page_contains_livewire_component()
    {
        $user = User::factory()->create(['role' => 'hr']);
        $mpp = Mpp::create([
            'plan_name' => 'Test Detail',
            'department' => 'HR',
            'job_title' => 'HR Manager',
            'quota' => 1,
            'sla_days' => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
        ]);

        $this->actingAs($user)
            ->get(route('mpp.show', $mpp->id))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Mpp\MppDetail::class);
    }

    public function test_can_approve_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'To Approve',
            'department' => 'Finance',
            'job_title' => 'Accountant',
            'quota' => 1,
            'sla_days' => 30,
            'status' => \App\Enums\MppStatus::DRAFT,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        Livewire::test(\App\Livewire\Mpp\MppDetail::class, ['mppId' => $mpp->id])
            ->call('approve');

        $this->assertDatabaseHas('mpps', [
            'id' => $mpp->id,
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);
    }

    public function test_cannot_edit_closed_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Closed Plan',
            'department' => 'IT',
            'job_title' => 'Developer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Closed',
        ]);

        Livewire::test(\App\Livewire\Mpp\MppForm::class, ['id' => $mpp->id])
            ->assertRedirect(route('mpp.index'));
    }

    public function test_cannot_delete_closed_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Closed Plan to Delete',
            'department' => 'IT',
            'job_title' => 'Developer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Closed',
        ]);

        Livewire::test(\App\Livewire\Mpp\MppIndex::class)
            ->call('delete', $mpp->id);

        $this->assertDatabaseHas('mpps', ['id' => $mpp->id]);
    }

    public function test_cannot_edit_completed_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Filled Plan',
            'department' => 'IT',
            'job_title' => 'Developer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);

        $rr = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp->id,
            'job_title' => 'Developer',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $lowongan = \App\Models\Lowongan::create([
            'recruitment_request_id' => $rr->id,
            'job_title' => 'Developer',
            'department' => 'IT',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'job_description' => 'Kerja Developer',
            'job_requirements' => 'Minimal S1',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        \App\Models\Candidate::create([
            'lowongan_id' => $lowongan->id,
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'phone' => '1234567890',
            'current_stage_id' => 1,
            'status' => 'Hired',
        ]);

        $this->assertEquals('Completed', $mpp->getComputedStatus());

        Livewire::test(\App\Livewire\Mpp\MppForm::class, ['id' => $mpp->id])
            ->assertRedirect(route('mpp.index'));
    }

    public function test_cannot_delete_completed_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Filled Plan to Delete',
            'department' => 'IT',
            'job_title' => 'Developer',
            'quota' => 1,
            'sla_days' => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);

        $rr = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp->id,
            'job_title' => 'Developer',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);

        $lowongan = \App\Models\Lowongan::create([
            'recruitment_request_id' => $rr->id,
            'job_title' => 'Developer',
            'department' => 'IT',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'job_description' => 'Kerja Developer',
            'job_requirements' => 'Minimal S1',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        \App\Models\Candidate::create([
            'lowongan_id' => $lowongan->id,
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'phone' => '1234567890',
            'current_stage_id' => 1,
            'status' => 'Hired',
        ]);

        $this->assertEquals('Completed', $mpp->getComputedStatus());

        Livewire::test(\App\Livewire\Mpp\MppIndex::class)
            ->call('delete', $mpp->id);

        $this->assertDatabaseHas('mpps', ['id' => $mpp->id]);
    }
}
