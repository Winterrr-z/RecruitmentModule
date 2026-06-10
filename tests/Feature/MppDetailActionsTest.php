<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mpp;
use App\Models\Vacancy;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MppDetailActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'hr']);
    }

    /** approve() mengubah status MPP Draft menjadi Approved */
    public function test_approve_draft_mpp(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'To Approve',
            'department'           => 'IT',
            'job_title'            => 'Developer',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::DRAFT,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->call('approve');

        $this->assertDatabaseHas('mpps', [
            'id'     => $mpp->id,
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);
    }

    /** closePlan() berhasil menutup MPP Approved yang tidak ada kandidat aktif */
    public function test_close_plan_approved_mpp_with_no_active_candidates(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'To Close',
            'department'           => 'Finance',
            'job_title'            => 'Accountant',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::APPROVED,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->call('closePlan');

        $this->assertDatabaseHas('mpps', [
            'id'     => $mpp->id,
            'status' => \App\Enums\MppStatus::CLOSED,
        ]);
    }

    /**
     * closePlan() gagal jika masih ada kandidat aktif di vacancy yang berelasi.
     * MppService::close() mengecek hasActiveCandidates() — bukan hasActiveRr().
     */
    public function test_close_plan_fails_when_has_active_candidates(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'Active Candidate Plan',
            'department'           => 'Marketing',
            'job_title'            => 'Marketing Manager',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::APPROVED,
        ]);

        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Marketing Manager',
            'department'           => 'Marketing',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Marketing Manager',
            'department'           => 'Marketing',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        // Kandidat aktif (status Applied = bukan Rejected/Hired/Withdrawn)
        Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Active Candidate',
            'email'            => 'active@example.com',
            'phone'            => '0877777777',
            'current_stage_id' => 1,
            'status'           => 'Applied',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->call('closePlan');

        // Status harus tetap Approved (tidak berubah)
        $this->assertDatabaseHas('mpps', [
            'id'     => $mpp->id,
            'status' => \App\Enums\MppStatus::APPROVED,
        ]);
    }

    /** closePlan() berhasil pada MPP Draft (belum di-approve) */
    public function test_close_plan_succeeds_for_draft_mpp(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'Draft Plan',
            'department'           => 'HR',
            'job_title'            => 'HR Specialist',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::DRAFT,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->call('closePlan');

        $this->assertDatabaseHas('mpps', [
            'id'     => $mpp->id,
            'status' => \App\Enums\MppStatus::CLOSED,
        ]);
    }

    /** hasVacancy benar saat MPP punya RR terkait */
    public function test_has_vacancy_is_true_when_rr_exists(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'MPP With RR',
            'department'           => 'IT',
            'job_title'            => 'Backend Dev',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::APPROVED,
        ]);

        \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Backend Dev',
            'department'           => 'IT',
            'status'               => 'Ready to Publish',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->assertSet('hasVacancy', true);
    }

    /** hasVacancy false saat MPP tidak punya RR */
    public function test_has_vacancy_is_false_when_no_rr(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'MPP Without RR',
            'department'           => 'IT',
            'job_title'            => 'Frontend Dev',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::DRAFT,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Mpp\MppDetail::class, ['id' => $mpp->id])
            ->assertSet('hasVacancy', false);
    }

    /** Halaman MPP detail memerlukan autentikasi */
    public function test_mpp_detail_requires_auth(): void
    {
        $mpp = Mpp::create([
            'plan_name'            => 'Auth Test Plan',
            'department'           => 'IT',
            'job_title'            => 'Dev',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $this->get(route('mpp.show', $mpp->id))
            ->assertRedirect(route('login'));
    }
}
