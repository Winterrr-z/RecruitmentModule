<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mpp;
use App\Models\Rr;
use App\Models\Vacancy;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RRDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Mpp $mpp;
    private Rr $rr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        $this->mpp = Mpp::create([
            'plan_name'            => 'MPP RR Detail Test',
            'department'           => 'Engineering',
            'job_title'            => 'Software Engineer',
            'quota'                => 2,
            'sla_days'             => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
            'status'               => \App\Enums\MppStatus::APPROVED,
        ]);

        $this->rr = Rr::create([
            'mpp_id'               => $this->mpp->id,
            'job_title'            => 'Software Engineer',
            'department'           => 'Engineering',
            'status'               => 'Ready to Publish',
            'job_description'      => 'Deskripsi pekerjaan',
            'job_requirements'     => 'Persyaratan kerja',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',   // valid: on-site | remote (bukan 'hybrid')
            'application_deadline' => now()->addDays(30)->format('Y-m-d'),
            'quota'                => 2,
        ]);
    }

    /** Helper: buat vacancy terkait RR */
    private function makeVacancy(array $overrides = []): Vacancy
    {
        return Vacancy::create(array_merge([
            'rr_id'                => $this->rr->id,
            'job_title'            => 'Software Engineer',
            'department'           => 'Engineering',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(30)->format('Y-m-d'),
            'quota'                => 2,
        ], $overrides));
    }

    /** Halaman RR detail memerlukan autentikasi */
    public function test_rr_detail_page_requires_auth(): void
    {
        $this->get(route('rr.show', $this->rr->id))
            ->assertRedirect(route('login'));
    }

    /** publish() mengubah status RR menjadi Published */
    public function test_publish_changes_status_to_published(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('publish');

        $this->assertDatabaseHas('rrs', [
            'id'     => $this->rr->id,
            'status' => 'Published',
        ]);
    }

    /** publish() membuat atau update vacancy secara otomatis */
    public function test_publish_creates_vacancy_automatically(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('publish');

        $this->assertDatabaseHas('vacancies', [
            'rr_id'     => $this->rr->id,
            'job_title' => 'Software Engineer',
            'status'    => 'Published',
        ]);
    }

    /** unpublish() mengubah status RR dari Published menjadi Ready to Publish */
    public function test_unpublish_changes_status_to_ready_to_publish(): void
    {
        $this->rr->update(['status' => 'Published']);
        $this->makeVacancy(['status' => 'Published']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('unpublish');

        $this->assertDatabaseHas('rrs', [
            'id'     => $this->rr->id,
            'status' => 'Ready to Publish',
        ]);

        $this->assertDatabaseHas('vacancies', [
            'rr_id'  => $this->rr->id,
            'status' => 'Draft',
        ]);
    }

    /** close() mengubah status RR menjadi Closed */
    public function test_close_changes_status_to_closed(): void
    {
        // RR must be Ready to Publish to be closed manually
        $this->rr->update(['status' => 'Ready to Publish']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('close');

        $this->assertDatabaseHas('rrs', [
            'id'     => $this->rr->id,
            'status' => 'Closed',
        ]);
    }

    /** close() juga menutup vacancy terkait jika ada */
    public function test_close_also_closes_associated_vacancy(): void
    {
        // Simulasi RR yang pernah di-publish lalu di-unpublish
        $this->rr->update(['status' => 'Ready to Publish']);
        $vacancy = $this->makeVacancy(['status' => 'Draft']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('close');

        $this->assertDatabaseHas('vacancies', [
            'id'     => $vacancy->id,
            'status' => 'Closed',
        ]);
    }

    /** delete() menghapus RR berstatus Ready to Publish dan redirect ke index */
    public function test_delete_draft_rr_and_redirects(): void
    {
        // RR masih dalam status Ready to Publish
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('delete')
            ->assertRedirect(route('rr.index'));

        $this->assertDatabaseMissing('rrs', ['id' => $this->rr->id]);
    }

    /** delete() gagal jika RR sudah Published */
    public function test_cannot_delete_published_rr(): void
    {
        $this->rr->update(['status' => 'Published']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('delete');

        $this->assertDatabaseHas('rrs', ['id' => $this->rr->id]);
    }

    /** delete() gagal jika ada kandidat berstatus Hired */
    public function test_cannot_delete_rr_with_hired_candidate(): void
    {
        $this->rr->update(['status' => 'Published']);
        $vacancy = $this->makeVacancy();

        Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Hired Person',
            'email'            => 'hired@example.com',
            'phone'            => '0899999999',
            'current_stage_id' => 1,
            'status'           => 'Hired',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Rr\RRDetail::class, ['id' => $this->rr->id])
            ->call('delete');

        $this->assertDatabaseHas('rrs', ['id' => $this->rr->id]);
    }
}
