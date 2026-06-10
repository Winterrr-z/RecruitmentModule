<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mpp;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RRTest extends TestCase
{
    use RefreshDatabase;

    public function test_rr_index_page_contains_livewire_component()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('rr.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Rr\RRIndex::class);
    }

    public function test_cannot_create_rr_from_draft_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Test Plan',
            'department' => 'IT',
            'job_title' => 'Engineer',
            'quota' => 1,
            'sla_days' => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
            'status' => \App\Enums\MppStatus::DRAFT,
        ]);

        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('rr.create', ['mppId' => $mpp->id]))
            ->assertRedirect(route('rr.index'))
            ->assertSessionHas('error');
    }

    public function test_can_create_rr_from_approved_mpp()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Approved Plan',
            'department' => 'Finance',
            'job_title' => 'Accountant',
            'quota' => 1,
            'sla_days' => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);

        Livewire::test(\App\Livewire\Rr\RRForm::class, ['mppId' => $mpp->id])
            ->set('job_description', 'Tugas Akuntan')
            ->set('job_requirements', 'Lulusan S1 Akuntansi')
            ->set('employment_type', 'full-time')
            ->set('location', 'on-site')
            ->set('application_deadline', now()->addDays(10)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('rr.index'));

        $this->assertDatabaseHas('rrs', [
            'mpp_id' => $mpp->id,
            'job_title' => 'Accountant',
            'status' => 'Ready to Publish',
            'employment_type' => 'full-time',
            'location' => 'on-site',
        ]);
    }

    public function test_rr_detail_page_contains_livewire_component()
    {
        $mpp = Mpp::create([
            'plan_name' => 'Test Detail',
            'department' => 'HR',
            'job_title' => 'HR Manager',
            'quota' => 1,
            'sla_days' => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
            'status' => \App\Enums\MppStatus::APPROVED,
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
            'job_title' => 'HR Manager',
            'department' => 'HR',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'job_description' => 'Kerja HR',
            'job_requirements' => 'Minimal S1',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Ready to Publish',
            'quota' => 1,
        ]);

        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('rr.show', $rr_temp->id))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Rr\RRDetail::class);
    }
}
