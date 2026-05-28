<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileHrTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pastikan rute profile detail membutuhkan login (auth).
     */
    public function test_profile_page_requires_auth()
    {
        $this->get(route('hr.profile'))
            ->assertRedirect(route('login'));
    }

    /**
     * Pastikan rute edit profile membutuhkan login (auth).
     */
    public function test_edit_profile_page_requires_auth()
    {
        $this->get(route('hr.profile.edit'))
            ->assertRedirect(route('login'));
    }

    /**
     * Pastikan pengguna HR terautentikasi dapat melihat halaman detail profile.
     */
    public function test_hr_can_view_profile_page()
    {
        $user = User::factory()->create([
            'role'         => 'hr',
            'departemen'   => 'Human Resources',
            'job_title'    => 'HR Specialist',
            'phone_number' => '08123456789'
        ]);

        $this->actingAs($user)
            ->get(route('hr.profile'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\ProfileHr::class);
    }

    /**
     * Pastikan halaman detail profile menampilkan data user.
     */
    public function test_profile_detail_shows_user_data()
    {
        $user = User::factory()->create([
            'role'         => 'hr',
            'name'         => 'Budi Santoso',
            'email'        => 'budi@example.com',
            'departemen'   => 'Human Resources',
            'job_title'    => 'HR Specialist',
            'phone_number' => '08123456789'
        ]);

        $this->actingAs($user)
            ->get(route('hr.profile'))
            ->assertSuccessful()
            ->assertSee('Budi Santoso')
            ->assertSee('budi@example.com')
            ->assertSee('Human Resources')
            ->assertSee('HR Specialist')
            ->assertSee('08123456789');
    }

    /**
     * Pastikan halaman detail profile menampilkan "Belum diisi" untuk field kosong.
     */
    public function test_profile_detail_shows_placeholder_for_empty_fields()
    {
        $user = User::factory()->create([
            'role'         => 'hr',
            'departemen'   => null,
            'job_title'    => null,
            'phone_number' => null,
        ]);

        $this->actingAs($user)
            ->get(route('hr.profile'))
            ->assertSuccessful()
            ->assertSee('Belum diisi');
    }

    /**
     * Pastikan halaman detail profile berisi link ke edit profile.
     */
    public function test_profile_detail_has_edit_button()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('hr.profile'))
            ->assertSuccessful()
            ->assertSee('Edit Profil')
            ->assertSee(route('hr.profile.edit'));
    }

    /**
     * Pastikan pengguna HR dapat mengakses halaman edit profile.
     */
    public function test_hr_can_view_edit_profile_page()
    {
        $user = User::factory()->create([
            'role'         => 'hr',
            'departemen'   => 'IT',
            'job_title'    => 'Developer',
            'phone_number' => '111',
        ]);

        $this->actingAs($user)
            ->get(route('hr.profile.edit'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\EditProfileHr::class);
    }

    /**
     * Pastikan data profile dapat diperbarui dengan sukses.
     */
    public function test_hr_can_update_profile()
    {
        $user = User::factory()->create([
            'role'         => 'hr',
            'name'         => 'Original Name',
            'email'        => 'original@example.com',
            'departemen'   => 'IT',
            'job_title'    => 'Programmer',
            'phone_number' => '111'
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Hr\EditProfileHr::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('departemen', 'HR')
            ->set('job_title' , 'HR Manager')
            ->set('phone_number', '222')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'           => $user->id,
            'name'         => 'Updated Name',
            'email'        => 'updated@example.com',
            'departemen'   => 'HR',
            'job_title'    => 'HR Manager',
            'phone_number' => '222',
        ]);
    }

    /**
     * Pastikan validasi form profil bekerja dengan benar.
     */
    public function test_profile_validation_works()
    {
        $user1 = User::factory()->create([
            'role'  => 'hr',
            'email' => 'user1@example.com'
        ]);
        $user2 = User::factory()->create([
            'role'  => 'hr',
            'email' => 'user2@example.com'
        ]);

        $this->actingAs($user1);

        Livewire::test(\App\Livewire\Hr\EditProfileHr::class)
            ->set('name', '')
            ->set('email', 'user2@example.com')
            ->call('save')
            ->assertHasErrors([
                'name'  => 'required',
                'email' => 'unique',
            ]);
    }
}
