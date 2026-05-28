<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mpp;
use App\Models\Lowongan;
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
            'nama_plan' => 'Test Plan',
            'departemen' => 'IT',
            'jabatan' => 'Engineer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 60,
            'target_waktu_absolut' => now()->addDays(60)->format('Y-m-d'),
            'status' => 'draft',
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
            'nama_plan' => 'Approved Plan',
            'departemen' => 'Finance',
            'jabatan' => 'Accountant',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 60,
            'target_waktu_absolut' => now()->addDays(60)->format('Y-m-d'),
            'status' => 'approved',
        ]);

        Livewire::test(\App\Livewire\Rr\RRForm::class, ['mppId' => $mpp->id])
            ->set('deskripsi_pekerjaan', 'Tugas Akuntan')
            ->set('spesifikasi_kebutuhan', 'Lulusan S1 Akuntansi')
            ->set('tipe_kerja', 'full-time')
            ->set('lokasi', 'on-site')
            ->set('application_deadline', now()->addDays(10)->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('rr.index'));

        $this->assertDatabaseHas('lowongans', [
            'mpp_id' => $mpp->id,
            'jabatan' => 'Accountant',
            'status' => 'Ready to Publish',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
        ]);
    }

    public function test_rr_detail_page_contains_livewire_component()
    {
        $mpp = Mpp::create([
            'nama_plan' => 'Test Detail',
            'departemen' => 'HR',
            'jabatan' => 'HR Manager',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 60,
            'target_waktu_absolut' => now()->addDays(60)->format('Y-m-d'),
            'status' => 'approved',
        ]);

        $lowongan = Lowongan::create([
            'mpp_id' => $mpp->id,
            'jabatan' => 'HR Manager',
            'departemen' => 'HR',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja HR',
            'spesifikasi_kebutuhan' => 'Minimal S1',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Ready to Publish',
            'kuota' => 1,
        ]);

        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('rr.show', $lowongan->id))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Rr\RRDetail::class);
    }
}
