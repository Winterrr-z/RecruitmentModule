<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicantAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_contains_livewire_component()
    {
        $this->get(route('candidate.register'))
            ->assertSuccessful()
            ->assertSeeLivewire('register-applicant');
    }

    public function test_login_page_contains_livewire_component()
    {
        $this->get(route('candidate.login'))
            ->assertSuccessful()
            ->assertSeeLivewire('login-applicant');
    }

    public function test_applicant_can_register()
    {
        Livewire::test('register-applicant')
            ->set('name', 'John Doe')
            ->set('email', 'johndoe@example.com')
            ->set('password', 'Secret123')
            ->set('password_confirmation', 'Secret123')
            ->call('register')
            ->assertRedirect(route('candidate.dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'role' => 'applicant',
        ]);

        $this->assertTrue(auth()->check());
        $this->assertEquals('johndoe@example.com', auth()->user()->email);
    }

    public function test_registration_validation_works()
    {
        // Email unique validation
        User::factory()->create([
            'email' => 'existing@example.com',
            'role' => 'applicant',
        ]);

        Livewire::test('register-applicant')
            ->set('name', '')
            ->set('email', 'existing@example.com')
            ->set('password', 'short')
            ->set('password_confirmation', 'mismatch')
            ->call('register')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'unique',
                'password' => 'confirmed',
            ]);
    }

    public function test_applicant_can_login()
    {
        $user = User::factory()->create([
            'email' => 'applicant@example.com',
            'password' => bcrypt('Secret123'),
            'role' => 'applicant',
        ]);

        Livewire::test('login-applicant')
            ->set('email', 'applicant@example.com')
            ->set('password', 'Secret123')
            ->call('login')
            ->assertRedirect(route('candidate.dashboard'));

        $this->assertTrue(auth()->check());
        $this->assertEquals($user->id, auth()->id());
    }

    public function test_login_validation_and_failed_attempts()
    {
        app(\Illuminate\Cache\RateLimiter::class)->clear('applicant@example.com|127.0.0.1');

        $user = User::factory()->create([
            'email' => 'applicant@example.com',
            'password' => bcrypt('Secret123'),
            'role' => 'applicant',
        ]);

        Livewire::test('login-applicant')
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email', 'password']);

        // Failed attempts rate limiting checks
        $component = Livewire::test('login-applicant')
            ->set('email', 'applicant@example.com')
            ->set('password', 'wrong-password');

        // 1st failed attempt
        $component->call('login')
            ->assertSet('authError', 'Email atau password salah.')
            ->assertSet('attemptsLeft', null);

        // 2nd failed attempt
        $component->set('password', 'wrong-password')
            ->call('login')
            ->assertSet('authError', 'Email atau password salah.')
            ->assertSet('attemptsLeft', null);

        // 3rd failed attempt
        $component->set('password', 'wrong-password')
            ->call('login')
            ->assertSet('authError', 'Email atau password salah.')
            ->assertSet('attemptsLeft', 2); // 5 - 3 = 2 left

        // 4th failed attempt
        $component->set('password', 'wrong-password')
            ->call('login')
            ->assertSet('authError', 'Email atau password salah.')
            ->assertSet('attemptsLeft', 1); // 5 - 4 = 1 left

        // 5th failed attempt
        $component->set('password', 'wrong-password')
            ->call('login')
            ->assertSet('attemptsLeft', 0)
            ->assertSee('Terlalu banyak percobaan');
    }
}
