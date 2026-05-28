<?php

namespace App\Livewire\Hr;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Class ResetPasswordHr
 *
 * Halaman reset password HR: input password baru menggunakan token dari email.
 *
 * @package App\Livewire\Hr
 */
class ResetPasswordHr extends Component
{
    /** @var string Token reset password. */
    public string $token = '';

    /** @var string Alamat email. */
    public string $email = '';

    /** @var string Password baru. */
    public string $password = '';

    /** @var string Konfirmasi password baru. */
    public string $password_confirmation = '';

    /** @var string|null Pesan status sukses. */
    public ?string $status = null;

    /**
     * Inisialisasi token dan email dari URL.
     */
    public function mount(string $token, ?string $email = null): void
    {
        $this->token = $token;
        $this->email = $email ?? request()->query('email', '');
    }

    /**
     * Reset password pengguna.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex'     => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        $result = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($result === Password::PASSWORD_RESET) {
            session()->flash('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
            $this->redirect(route('hr.login'), navigate: true);
        } else {
            $this->addError('email', 'Token tidak valid atau sudah kedaluwarsa. Silakan minta link reset baru.');
        }
    }

    /**
     * Render komponen.
     */
    public function render()
    {
        return view('livewire.hr.reset-password-hr')
            ->layout('layouts.guest');
    }
}
