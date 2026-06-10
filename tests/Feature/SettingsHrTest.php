<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsHrTest extends TestCase
{
    use RefreshDatabase;

    /** Halaman settings memerlukan autentikasi */
    public function test_settings_page_requires_auth(): void
    {
        $this->get(route('hr.settings'))
            ->assertRedirect(route('login'));
    }

    /** Halaman settings dapat diakses oleh HR yang login */
    public function test_settings_page_is_accessible_by_hr(): void
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('hr.settings'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\SettingsHr::class);
    }
}
