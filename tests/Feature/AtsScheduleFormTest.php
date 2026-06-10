<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsScheduleFormTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Stage $stage;
    private Candidate $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        // Gunakan Stage::create() langsung agar default_location pasti tersimpan,
        // tanpa bergantung pada firstOrCreate yang bisa mengembalikan record lama
        // dari test sebelumnya.
        $this->stage = Stage::create([
            'name'                 => 'Wawancara HR Lanjutan',
            'description'          => 'Tahap wawancara lanjut',
            'sequence'             => 99,
            'needs_schedule'       => true,
            'default_location'     => 'Ruang Meeting A',
            'default_virtual_link' => 'https://meet.example.com/hr',
        ]);

        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP Schedule Test',
            'department'           => 'IT',
            'job_title'            => 'QA Engineer',
            'quota'                => 2,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'QA Engineer',
            'department'           => 'IT',
            'status'               => 'Published',
            'job_description'      => 'Test Desc',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 2,
        ]);

        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'QA Engineer',
            'department'           => 'IT',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Job description',
            'job_requirements'     => 'Job requirements',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 2,
        ]);

        $this->candidate = Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Test Kandidat',
            'email'            => 'kandidat@example.com',
            'phone'            => '0812345678',
            'current_stage_id' => $this->stage->id,
            'status'           => 'Applied',
        ]);
    }

    /** Halaman form jadwal memerlukan autentikasi */
    public function test_schedule_form_page_requires_auth(): void
    {
        $this->get(route('ats.candidate.schedule', [
            'candidateId' => $this->candidate->id,
            'stageId'     => $this->stage->id,
        ]))->assertRedirect(route('login'));
    }

    /** mount() memuat default_location dan default_virtual_link dari Stage */
    public function test_mount_loads_stage_defaults_when_no_existing_schedule(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->assertSet('venue', 'Ruang Meeting A')
            ->assertSet('virtual_link', 'https://meet.example.com/hr');
    }

    /** mount() memuat jadwal yang sudah ada sebelumnya */
    public function test_mount_loads_existing_schedule(): void
    {
        InterviewSchedule::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'date'         => '2099-12-01',
            'time'         => '09:00',
            'venue'        => 'Kantor Pusat',
            'virtual_link' => null,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->assertSet('date', '2099-12-01')
            ->assertSet('time', '09:00')
            ->assertSet('venue', 'Kantor Pusat');
    }

    /** save() berhasil membuat jadwal baru dan redirect ke detail kandidat */
    public function test_save_creates_new_schedule_and_redirects(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '2099-11-20')
            ->set('time', '10:00')
            ->set('venue', 'Gedung B Lantai 3')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        $this->assertDatabaseHas('interview_schedules', [
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'time'         => '10:00',
            'venue'        => 'Gedung B Lantai 3',
        ]);
    }

    /** save() melakukan update jika jadwal sudah ada (updateOrCreate) */
    public function test_save_updates_existing_schedule(): void
    {
        InterviewSchedule::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'date'         => '2099-10-01',
            'time'         => '08:00',
            'venue'        => 'Ruang Lama',
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '2099-11-25')
            ->set('time', '14:30')
            ->set('venue', 'Ruang Baru')
            ->call('save')
            ->assertHasNoErrors();

        // Tetap hanya 1 record (updateOrCreate)
        $this->assertEquals(1, InterviewSchedule::where('candidate_id', $this->candidate->id)
            ->where('stage_id', $this->stage->id)
            ->count());

        $this->assertDatabaseHas('interview_schedules', [
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'venue'        => 'Ruang Baru',
        ]);
    }

    /** save() gagal validasi jika tanggal kosong */
    public function test_save_fails_validation_when_date_missing(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '')
            ->set('time', '10:00')
            ->call('save')
            ->assertHasErrors(['date' => 'required']);
    }

    /** save() gagal validasi jika waktu kosong */
    public function test_save_fails_validation_when_time_missing(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '2099-11-20')
            ->set('time', '')
            ->call('save')
            ->assertHasErrors(['time' => 'required']);
    }

    /** save() gagal validasi jika virtual_link bukan URL valid */
    public function test_save_fails_validation_when_virtual_link_invalid(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '2099-11-20')
            ->set('time', '10:00')
            ->set('virtual_link', 'bukan-url-valid')
            ->call('save')
            ->assertHasErrors(['virtual_link' => 'url']);
    }

    /** virtual_link boleh null (nullable) */
    public function test_save_accepts_null_virtual_link(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScheduleForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->set('date', '2099-11-20')
            ->set('time', '10:00')
            ->set('venue', 'Kantor Cabang')
            ->set('virtual_link', null)
            ->call('save')
            ->assertHasNoErrors();
    }
}
