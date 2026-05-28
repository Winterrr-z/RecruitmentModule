<?php

namespace App\Livewire\Hr;

use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Class LoginHr
 *
 * Form login khusus untuk HR.
 * Dilengkapi rate-limiting (max 5 percobaan/menit)
 * dan hanya mengizinkan user dengan role 'hr' untuk masuk.
 *
 * @package App\Livewire\Hr
 */
class LoginHr extends Component
{
    /** @var string Alamat email. */
    public string $email = '';

    /** @var string Kata sandi. */
    public string $password = '';

    /** @var bool Ingat sesi login. */
    public bool $remember = false;

    /** @var string|null Pesan error autentikasi. */
    public ?string $authError = null;

    /** @var int|null Sisa percobaan login. */
    public ?int $attemptsLeft = null;

    // Batas maksimum percobaan & durasi lockout (detik)
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    /**
     * Reset error saat email / password diubah.
     */
    public function updatedEmail(): void
    {
        $this->authError   = null;
        $this->attemptsLeft = null;
    }

    public function updatedPassword(): void
    {
        $this->authError   = null;
        $this->attemptsLeft = null;
    }

    /**
     * Kunci throttle unik berdasarkan email + IP.
     */
    private function throttleKey(): string
    {
        return 'login:hr:' . Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    /**
     * Proses login HR.
     */
    public function login()
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $limiter = app(RateLimiter::class);
        $key     = $this->throttleKey();

        // Cek apakah sedang terkunci
        if ($limiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->authError    = "Terlalu banyak percobaan. Akun terkunci selama 1 menit. Silakan coba lagi nanti.";
            $this->attemptsLeft = 0;
            return;
        }

        // Percobaan autentikasi
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();
            
            // Periksa apakah user memiliki role HR
            if ($user->role !== 'hr') {
                Auth::logout();
                $this->authError = 'Akses ditolak. Akun ini tidak terdaftar sebagai HR.';
                $this->password = '';
                return;
            }

            $limiter->clear($key);
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }
            return redirect()->intended(route('dashboard'));
        }

        // Gagal — catat hit & hitung sisa
        $limiter->hit($key, self::DECAY_SECONDS);
        $remaining = self::MAX_ATTEMPTS - $limiter->attempts($key);

        $this->authError = 'Email atau password salah.';

        if ($remaining <= 2 && $remaining > 0) {
            $this->attemptsLeft = $remaining;
        } elseif ($remaining <= 0) {
            $this->authError    = "Terlalu banyak percobaan. Akun terkunci selama 1 menit. Silakan coba lagi nanti.";
            $this->attemptsLeft = 0;
        }

        $this->password = '';
    }

    /**
     * Render komponen.
     */
    public function render()
    {
        return view('livewire.hr.login-hr')
            ->layout('layouts.guest');
    }
}
