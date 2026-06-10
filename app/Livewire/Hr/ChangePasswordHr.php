<?php

namespace App\Livewire\Hr;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

/**
 * Class ChangePasswordHr
 *
 * Halaman ubah password HR dari dalam akun (sudah login).
 * User wajib memasukkan password lama dan password baru.
 *
 * @package App\Livewire\Hr
 */
class ChangePasswordHr extends Component
{
    /** @var string Password lama. */
    public string $current_password = '';

    /** @var string Password baru. */
    public string $password = '';

    /** @var string Konfirmasi password baru. */
    public string $password_confirmation = '';

    /**
     * Ubah password pengguna.
     */
    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.min'              => 'Password baru minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
            'password.regex'            => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        $user = Auth::user();

        // Verifikasi password lama
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini salah.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // Reset form
        $this->current_password      = '';
        $this->password              = '';
        $this->password_confirmation = '';

        session()->flash('success', 'Password berhasil diubah.');

        $this->redirect(route('hr.profile'), navigate: true);
    }

    /**
     * Render komponen.
     */
    public function render()
    {
        return view('livewire.hr.change-password-hr')
            ->layout('layouts.hr');
    }
}
