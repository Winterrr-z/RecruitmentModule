<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\Candidate;
use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AtsManualAndBlacklistTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        $mpp = \App\Models\Mpp::create([
            'nama_plan' => 'Staff Plan',
            'departemen' => 'Sales',
            'jabatan' => 'Sales Staff',
            'jumlah_kebutuhan' => 2,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $this->job = Lowongan::create([
            'mpp_id' => $mpp->id,
            'jabatan' => 'Sales Staff',
            'departemen' => 'Sales',
            'status' => 'Published',
            'expected_join_date' => now()->addDays(30)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Job description',
            'spesifikasi_kebutuhan' => 'Job requirements',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 2,
        ]);
    }

    public function test_manual_candidate_creation_saves_to_db()
    {
        Storage::fake('local');
        $dummyCv = UploadedFile::fake()->create('my_cv.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsManualCandidate::class, ['lowonganId' => $this->job->id])
            ->set('nama', 'Alice Johnson')
            ->set('email', 'alice@example.com')
            ->set('telepon', '0899999999')
            ->set('cv', $dummyCv)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.dashboard', ['selectedLowonganId' => $this->job->id]));

        $this->assertDatabaseHas('candidates', [
            'nama' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'source' => 'manual',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);
    }

    public function test_blacklist_manages_records_and_picker()
    {
        // 1. Create a candidate to test the picker
        $cand = Candidate::create([
            'lowongan_id' => $this->job->id,
            'nama' => 'Bad Guy',
            'email' => 'bad@example.com',
            'telepon' => '0866666666',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);

        // 2. Test Livewire component Blacklist
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsBlacklist::class)
            ->set('search', '')
            // Trigger picker search
            ->set('candidateSearch', 'Bad')
            ->call('selectCandidate', $cand->id)
            ->assertSet('nama', 'Bad Guy')
            ->assertSet('email', 'bad@example.com')
            ->assertSet('telepon', '0866666666')
            ->set('alasan', 'Fraud detected')
            ->call('save')
            ->assertHasNoErrors();

        // Check blacklist row added
        $this->assertDatabaseHas('blacklist', [
            'nama' => 'Bad Guy',
            'email' => 'bad@example.com',
            'alasan' => 'Fraud detected',
        ]);

        // Check active candidate auto-rejected
        $this->assertEquals('Ditolak', $cand->fresh()->status);

        // 3. Test blacklist delete
        $blacklistRow = Blacklist::where('email', 'bad@example.com')->first();
        
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsBlacklist::class)
            ->call('deleteBlacklist', $blacklistRow->id);

        $this->assertDatabaseMissing('blacklist', [
            'email' => 'bad@example.com',
        ]);
    }

    public function test_can_create_manual_candidate_without_lowongan()
    {
        Storage::fake('local');
        $dummyCv = UploadedFile::fake()->create('my_cv.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsManualCandidate::class)
            ->set('nama', 'Independent Candidate')
            ->set('email', 'independent@example.com')
            ->set('telepon', '0899999998')
            ->set('cv', $dummyCv)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.dashboard'));

        $this->assertDatabaseHas('candidates', [
            'nama' => 'Independent Candidate',
            'email' => 'independent@example.com',
            'lowongan_id' => null,
            'source' => 'manual',
            'current_stage_id' => 1,
            'status' => 'Applied',
        ]);
    }
}
