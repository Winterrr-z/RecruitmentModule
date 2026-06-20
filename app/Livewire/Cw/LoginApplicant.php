<?php

namespace App\Livewire\Cw;

use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class LoginApplicant
 *
 * Komponen Livewire untuk formulir masuk (login) bagi pelamar.
 * Dilengkapi pembatasan frekuensi (rate-limiting) maksimal 5 percobaan per menit
 * untuk setiap kombinasi alamat email dan IP, serta pesan sisa percobaan 
 * saat mendekati batas pemblokiran.
 *
 * @package App\Livewire\Cw
 */
#[Layout('layouts.auth')]
class LoginApplicant extends Component
{
    /** @var string Alamat email. */
    public string $email = '';

    /** @var string Kata sandi. */
    public string $password = '';

    /** @var bool Ingat sesi login. */
    public bool $remember = false;

    /** @var string|null Pesan error autentikasi. */
    public ?string $authError = null;

    /** @var int|null Sisa percobaan login (ditampilkan jika ≤ 2). */
    public ?int $attemptsLeft = null;

    // Batas maksimum percobaan & durasi lockout (detik)
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    /**
     * Menghapus pesan error ketika pengguna mengetik ulang email atau kata sandi.
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
     * Membuat kunci identifikasi (throttle key) unik berdasarkan email dan alamat IP.
     * Digunakan untuk pembatasan jumlah percobaan login.
     * 
     * @return string Kunci rate-limiting.
     */
    private function throttleKey(): string
    {
        return 'login:' . Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }

    /**
     * Memproses percobaan login pelamar.
     * Melakukan validasi input, pengecekan rate-limit, dan autentikasi.
     *
     * @return \Illuminate\Http\RedirectResponse|void
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

            // Cek apakah kandidat di-blacklist
            if (\App\Models\Blacklist::where('email', $user->email)->exists()) {
                Auth::logout();
                $limiter->clear($key);
                return redirect()->route('blacklist.info');
            }

            // Periksa apakah user memiliki role applicant
            if ($user->role !== 'applicant') {
                Auth::logout();
                $this->authError = 'Halaman ini khusus pelamar. Silakan login melalui portal HR.';
                $this->password = '';
                return;
            }

            $limiter->clear($key);
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }
            return redirect()->intended(route('candidate.dashboard'));
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
     * Render komponen antarmuka halaman login pelamar.
     */
    public function render()
    {
        return view('livewire.cw.login-applicant');
    }
}
