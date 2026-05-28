<?php

namespace App\Livewire\Hr;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

/**
 * Class ForgotPasswordHr
 *
 * Halaman lupa password HR: input email lalu kirim link reset password.
 *
 * @package App\Livewire\Hr
 */
class ForgotPasswordHr extends Component
{
    /** @var string Alamat email HR. */
    public string $email = '';

    /** @var string|null Pesan status berhasil. */
    public ?string $status = null;

    /**
     * Kirim link reset password ke email.
     */
    public function sendResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $result = Password::sendResetLink(['email' => $this->email]);

        if ($result === Password::RESET_LINK_SENT) {
            $this->status = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.';
            $this->email  = '';
        } else {
            $this->addError('email', 'Kami tidak dapat menemukan akun dengan alamat email tersebut.');
        }
    }

    /**
     * Render komponen.
     */
    public function render()
    {
        return view('livewire.hr.forgot-password-hr')
            ->layout('layouts.guest');
    }
}
