<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordHrTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Forgot Password
    // -----------------------------------------------------------------------

    /**
     * Halaman forgot password dapat diakses.
     */
    public function test_forgot_password_page_is_accessible()
    {
        $this->get(route('hr.password.request'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\ForgotPasswordHr::class);
    }

    /**
     * Validasi email required pada forgot password.
     */
    public function test_forgot_password_validates_email()
    {
        Livewire::test(\App\Livewire\Hr\ForgotPasswordHr::class)
            ->set('email', '')
            ->call('sendResetLink')
            ->assertHasErrors(['email' => 'required']);
    }

    /**
     * Email tidak terdaftar menampilkan error.
     */
    public function test_forgot_password_invalid_email_shows_error()
    {
        Livewire::test(\App\Livewire\Hr\ForgotPasswordHr::class)
            ->set('email', 'tidak-ada@example.com')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    /**
     * Email terdaftar berhasil mengirim link reset.
     */
    public function test_forgot_password_sends_reset_link()
    {
        $user = User::factory()->create([
            'role'  => 'hr',
            'email' => 'hr@example.com',
        ]);

        Livewire::test(\App\Livewire\Hr\ForgotPasswordHr::class)
            ->set('email', 'hr@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('status', 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
    }

    // -----------------------------------------------------------------------
    // Reset Password
    // -----------------------------------------------------------------------

    /**
     * Halaman reset password dapat diakses dengan token.
     */
    public function test_reset_password_page_is_accessible()
    {
        $this->get(route('password.reset', ['token' => 'fake-token', 'email' => 'test@example.com']))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\ResetPasswordHr::class);
    }

    /**
     * Validasi password baru di reset password.
     */
    public function test_reset_password_validates_password()
    {
        Livewire::test(\App\Livewire\Hr\ResetPasswordHr::class, ['token' => 'fake'])
            ->set('email', '')
            ->set('password', '123')
            ->set('password_confirmation', '456')
            ->call('resetPassword')
            ->assertHasErrors(['email', 'password']);
    }

    /**
     * Token tidak valid menampilkan error.
     */
    public function test_reset_password_invalid_token_shows_error()
    {
        User::factory()->create([
            'role'  => 'hr',
            'email' => 'hr@example.com',
        ]);

        Livewire::test(\App\Livewire\Hr\ResetPasswordHr::class, ['token' => 'invalid-token'])
            ->set('email', 'hr@example.com')
            ->set('password', 'NewPass123')
            ->set('password_confirmation', 'NewPass123')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }

    /**
     * Reset password berhasil dengan token valid.
     */
    public function test_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'role'     => 'hr',
            'email'    => 'hr@example.com',
            'password' => Hash::make('OldPass123'),
        ]);

        $token = Password::createToken($user);

        Livewire::test(\App\Livewire\Hr\ResetPasswordHr::class, ['token' => $token])
            ->set('email', 'hr@example.com')
            ->set('password', 'NewPass123')
            ->set('password_confirmation', 'NewPass123')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('hr.login'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123', $user->password));
    }

    // -----------------------------------------------------------------------
    // Change Password (logged in)
    // -----------------------------------------------------------------------

    /**
     * Halaman ubah password membutuhkan auth.
     */
    public function test_change_password_requires_auth()
    {
        $this->get(route('hr.profile.password'))
            ->assertRedirect(route('login'));
    }

    /**
     * HR bisa mengakses halaman ubah password.
     */
    public function test_hr_can_view_change_password_page()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('hr.profile.password'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\ChangePasswordHr::class);
    }

    /**
     * Password lama salah menampilkan error.
     */
    public function test_change_password_wrong_current_password()
    {
        $user = User::factory()->create([
            'role'     => 'hr',
            'password' => Hash::make('CorrectOld1'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Hr\ChangePasswordHr::class)
            ->set('current_password', 'WrongOld123')
            ->set('password', 'NewPass123')
            ->set('password_confirmation', 'NewPass123')
            ->call('changePassword')
            ->assertHasErrors(['current_password']);
    }

    /**
     * Validasi password baru (min 8, uppercase, lowercase, digit).
     */
    public function test_change_password_validates_new_password()
    {
        $user = User::factory()->create([
            'role'     => 'hr',
            'password' => Hash::make('OldPass123'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Hr\ChangePasswordHr::class)
            ->set('current_password', 'OldPass123')
            ->set('password', 'weak')
            ->set('password_confirmation', 'weak')
            ->call('changePassword')
            ->assertHasErrors(['password']);
    }

    /**
     * Konfirmasi password tidak cocok menampilkan error.
     */
    public function test_change_password_confirmation_mismatch()
    {
        $user = User::factory()->create([
            'role'     => 'hr',
            'password' => Hash::make('OldPass123'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Hr\ChangePasswordHr::class)
            ->set('current_password', 'OldPass123')
            ->set('password', 'NewPass123')
            ->set('password_confirmation', 'DifferentPass123')
            ->call('changePassword')
            ->assertHasErrors(['password' => 'confirmed']);
    }

    /**
     * Password berhasil diubah.
     */
    public function test_change_password_success()
    {
        $user = User::factory()->create([
            'role'     => 'hr',
            'password' => Hash::make('OldPass123'),
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Hr\ChangePasswordHr::class)
            ->set('current_password', 'OldPass123')
            ->set('password', 'NewPass123')
            ->set('password_confirmation', 'NewPass123')
            ->call('changePassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('hr.profile'));

        $user->refresh();
        $this->assertTrue(Hash::check('NewPass123', $user->password));
    }
}
