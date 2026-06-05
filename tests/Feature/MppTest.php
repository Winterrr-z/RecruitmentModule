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
            ->set('nama_plan', 'Test Plan')
            ->set('departemen', 'IT')
            ->set('jabatan', 'Software Engineer')
            ->set('jumlah_kebutuhan', 2)
            ->set('estimasi_gaji_min', '10,000,000')
            ->set('estimasi_gaji_max', '15,000,000')
            ->set('sla_hari', 90)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('mpps', [
            'nama_plan' => 'Test Plan',
            'departemen' => 'IT',
            'jabatan' => 'Software Engineer',
            'jumlah_kebutuhan' => 2,
            'estimasi_gaji_min' => 10000000,
            'estimasi_gaji_max' => 15000000,
            'sla_hari' => 90,
            'status' => 'draft',
        ]);
    }

    public function test_mpp_detail_page_contains_livewire_component()
    {
        $user = User::factory()->create(['role' => 'hr']);
        $mpp = Mpp::create([
            'nama_plan' => 'Test Detail',
            'departemen' => 'HR',
            'jabatan' => 'HR Manager',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 60,
            'target_waktu_absolut' => now()->addDays(60)->format('Y-m-d'),
        ]);

        $this->actingAs($user)
            ->get(route('mpp.show', $mpp->id))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Mpp\MppDetail::class);
    }

    public function test_can_approve_mpp()
    {
        $mpp = Mpp::create([
            'nama_plan' => 'To Approve',
            'departemen' => 'Finance',
            'jabatan' => 'Accountant',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'status' => 'draft',
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
        ]);

        Livewire::test(\App\Livewire\Mpp\MppDetail::class, ['mppId' => $mpp->id])
            ->call('approve');

        $this->assertDatabaseHas('mpps', [
            'id' => $mpp->id,
            'status' => 'approved',
        ]);
    }

    public function test_cannot_edit_closed_mpp()
    {
        $mpp = Mpp::create([
            'nama_plan' => 'Closed Plan',
            'departemen' => 'IT',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Closed',
        ]);

        Livewire::test(\App\Livewire\Mpp\MppForm::class, ['id' => $mpp->id])
            ->assertRedirect(route('mpp.index'));
    }

    public function test_cannot_delete_closed_mpp()
    {
        $mpp = Mpp::create([
            'nama_plan' => 'Closed Plan to Delete',
            'departemen' => 'IT',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'Closed',
        ]);

        Livewire::test(\App\Livewire\Mpp\MppIndex::class)
            ->call('delete', $mpp->id);

        $this->assertDatabaseHas('mpps', ['id' => $mpp->id]);
    }

    public function test_cannot_edit_completed_mpp()
    {
        $mpp = Mpp::create([
            'nama_plan' => 'Filled Plan',
            'departemen' => 'IT',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'approved',
        ]);

        $rr = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp->id,
            'jabatan' => 'Developer',
            'departemen' => 'IT',
            'status' => 'Published',
            'deskripsi_pekerjaan' => 'Test Desc',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 1,
        ]);

        $lowongan = \App\Models\Lowongan::create([
            'recruitment_request_id' => $rr->id,
            'jabatan' => 'Developer',
            'departemen' => 'IT',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja Developer',
            'spesifikasi_kebutuhan' => 'Minimal S1',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        \App\Models\Candidate::create([
            'lowongan_id' => $lowongan->id,
            'nama' => 'John Doe',
            'email' => 'john@doe.com',
            'telepon' => '1234567890',
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
            'nama_plan' => 'Filled Plan to Delete',
            'departemen' => 'IT',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 1,
            'sla_hari' => 30,
            'target_waktu_absolut' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'approved',
        ]);

        $rr = \App\Models\RecruitmentRequest::create([
            'mpp_id' => $mpp->id,
            'jabatan' => 'Developer',
            'departemen' => 'IT',
            'status' => 'Published',
            'deskripsi_pekerjaan' => 'Test Desc',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'kuota' => 1,
        ]);

        $lowongan = \App\Models\Lowongan::create([
            'recruitment_request_id' => $rr->id,
            'jabatan' => 'Developer',
            'departemen' => 'IT',
            'expected_join_date' => now()->addDays(60)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja Developer',
            'spesifikasi_kebutuhan' => 'Minimal S1',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        \App\Models\Candidate::create([
            'lowongan_id' => $lowongan->id,
            'nama' => 'John Doe',
            'email' => 'john@doe.com',
            'telepon' => '1234567890',
            'current_stage_id' => 1,
            'status' => 'Hired',
        ]);

        $this->assertEquals('Completed', $mpp->getComputedStatus());

        Livewire::test(\App\Livewire\Mpp\MppIndex::class)
            ->call('delete', $mpp->id);

        $this->assertDatabaseHas('mpps', ['id' => $mpp->id]);
    }
}
