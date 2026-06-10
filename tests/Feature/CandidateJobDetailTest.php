<?php

namespace Tests\Feature;

use App\Models\Mpp;
use App\Models\Lowongan;
use App\Models\User;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CandidateJobDetailTest extends TestCase
{
    use RefreshDatabase;

    private $mpp;
    private $lowongan;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base data
        $this->mpp = Mpp::create([
            'plan_name' => 'Plan IT',
            'department' => 'Teknologi',
            'job_title' => 'Developer',
            'quota' => 3,
            'sla_days' => 90,
            'absolute_target_date' => now()->addDays(90)->format('Y-m-d'),
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
            'job_title' => 'Laravel Specialist',
            'department' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Develop clean code.',
            'job_requirements' => 'PHP 8.2 & Laravel 11/12',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addMonth()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 2,
        ]);
    }

    public function test_guest_is_redirected_to_login()
    {
        $this->get(route('candidate.jobs.show', $this->lowongan->id))
            ->assertRedirect(route('login'));
    }

    public function test_applicant_sees_prepopulated_form()
    {
        $user = User::factory()->create([
            'name' => 'Adit Permana',
            'email' => 'adit@example.com',
            'role' => 'applicant',
        ]);

        $this->actingAs($user)
            ->get(route('candidate.jobs.show', $this->lowongan->id))
            ->assertSuccessful()
            ->assertSee('Adit Permana')
            ->assertSee('adit@example.com')
            ->assertSee('Nomor Telepon');
    }

    public function test_validation_works_on_submit()
    {
        $user = User::factory()->create(['role' => 'applicant']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cw\CandidateJobDetail::class, ['id' => $this->lowongan->id])
            ->call('apply')
            ->assertHasErrors(['phone' => 'required', 'cv' => 'required']);
    }

    public function test_successful_application_submission()
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'applicant',
        ]);

        $cvFile = UploadedFile::fake()->create('cv.pdf', 1000, 'application/pdf');
        $portfolioFile = UploadedFile::fake()->create('portfolio.pdf', 2000, 'application/pdf');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cw\CandidateJobDetail::class, ['id' => $this->lowongan->id])
            ->set('phone', '0812345678')
            ->set('cv', $cvFile)
            ->set('portofolio', $portfolioFile)
            ->call('apply')
            ->assertRedirect(route('candidate.dashboard'));

        $this->assertDatabaseHas('candidates', [
            'lowongan_id' => $this->lowongan->id,
            'user_id' => $user->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '0812345678',
            'status' => 'Applied',
            'source' => 'public',
        ]);

        $candidate = Candidate::first();
        $this->assertNotNull($candidate->cv_path);
        $this->assertNotNull($candidate->portofolio_path);

        Storage::disk('local')->assertExists($candidate->cv_path);
        Storage::disk('local')->assertExists($candidate->portofolio_path);
    }

    public function test_blacklisted_applicant_redirects_to_blacklist_info()
    {
        // Seed blacklist
        DB::table('blacklist')->insert([
            'name' => 'Blacklisted Person',
            'email' => 'blacklist@example.com',
            'phone' => '0899999999',
            'reason' => 'Indisipliner',
        ]);

        $user = User::factory()->create([
            'name' => 'Bad Guy',
            'email' => 'blacklist@example.com',
            'role' => 'applicant',
        ]);

        $cvFile = UploadedFile::fake()->create('cv.pdf', 1000, 'application/pdf');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Cw\CandidateJobDetail::class, ['id' => $this->lowongan->id])
            ->set('phone', '0812345678') // blacklist by email
            ->set('cv', $cvFile)
            ->call('apply')
            ->assertRedirect(route('blacklist.info'));

        $this->assertDatabaseEmpty('candidates');
    }

    public function test_blacklist_info_page_is_accessible()
    {
        $this->get(route('blacklist.info'))
            ->assertSuccessful()
            ->assertSee('Pendaftaran Dibatasi')
            ->assertSee('Anda tidak dapat melamar lowongan ini karena data Anda terdaftar dalam daftar hitam perusahaan.');
    }
}
