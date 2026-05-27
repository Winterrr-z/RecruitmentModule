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
}
