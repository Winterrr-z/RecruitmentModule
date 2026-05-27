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
            'nama_plan' => 'Plan IT',
            'departemen' => 'Teknologi',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 3,
            'sla_bulan' => 3,
            'target_waktu_absolut' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $this->lowongan = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'Laravel Specialist',
            'departemen' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Develop clean code.',
            'spesifikasi_kebutuhan' => 'PHP 8.2 & Laravel 11/12',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addMonth()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 2,
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
            ->test('candidate-job-detail', ['id' => $this->lowongan->id])
            ->call('apply')
            ->assertHasErrors(['telepon' => 'required', 'cv' => 'required']);
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
            ->test('candidate-job-detail', ['id' => $this->lowongan->id])
            ->set('telepon', '0812345678')
            ->set('cv', $cvFile)
            ->set('portofolio', $portfolioFile)
            ->call('apply')
            ->assertRedirect(route('candidate.dashboard'));

        $this->assertDatabaseHas('candidates', [
            'lowongan_id' => $this->lowongan->id,
            'user_id' => $user->id,
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'telepon' => '0812345678',
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
            'nama' => 'Blacklisted Person',
            'email' => 'blacklist@example.com',
            'telepon' => '0899999999',
            'alasan' => 'Indisipliner',
        ]);

        $user = User::factory()->create([
            'name' => 'Bad Guy',
            'email' => 'blacklist@example.com',
            'role' => 'applicant',
        ]);

        $cvFile = UploadedFile::fake()->create('cv.pdf', 1000, 'application/pdf');

        Livewire::actingAs($user)
            ->test('candidate-job-detail', ['id' => $this->lowongan->id])
            ->set('telepon', '0812345678') // blacklist by email
            ->set('cv', $cvFile)
            ->call('apply')
            ->assertRedirect(route('blacklist.info'));

        $this->assertDatabaseEmpty('candidates');
    }
}
