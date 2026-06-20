<?php

namespace App\Livewire\Cw;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class RegisterApplicant
 *
 * Komponen Livewire untuk formulir pendaftaran akun (registrasi) pelamar baru.
 * Setelah pendaftaran berhasil, pengguna akan langsung diotentikasi (login otomatis)
 * dan diarahkan ke halaman dashboard pelamar.
 *
 * @package App\Livewire\Cw
 */
#[Layout('layouts.auth')]
class RegisterApplicant extends Component
{
    /** @var string Nama lengkap pelamar. */
    public string $name = '';

    /** @var string Alamat email. */
    public string $email = '';

    /** @var string Kata sandi. */
    public string $password = '';

    /** @var string Konfirmasi kata sandi. */
    public string $password_confirmation = '';

    /** @var string|null Pesan error autentikasi. */
    public ?string $authError = null;

    /** @var int|null Sisa percobaan pendaftaran. */
    public ?int $attemptsLeft = null;

    // Batas maksimum percobaan & durasi lockout (detik)
    private const MAX_ATTEMPTS = 2;
    private const DECAY_SECONDS = 600; // 10 menit

    /**
     * Membuat kunci identifikasi unik berdasarkan IP untuk membatasi pendaftaran berulang.
     *
     * @return string Kunci rate-limiting.
     */
    private function throttleKey(): string
    {
        return 'register:' . request()->ip();
    }

    /**
     * Menentukan aturan validasi untuk formulir registrasi.
     * Mengatur syarat kata sandi yang kuat (huruf besar, kecil, angka, dll).
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'confirmed',
            ],
        ];
    }

    /**
     * Menentukan pesan error kustom dalam Bahasa Indonesia untuk setiap validasi.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah terdaftar.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.string'    => 'Kata sandi harus berupa teks.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.regex'     => 'Kata sandi harus mengandung huruf besar, huruf kecil, dan angka.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }

    /**
     * Memproses pendaftaran pelamar baru.
     * Termasuk melakukan validasi pembatasan laju pembuatan akun (rate-limit), 
     * menghubungkan akun pengguna dengan data lamaran manual yang ada sebelumnya,
     * serta melakukan login otomatis.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function register()
    {
        $limiter = app(RateLimiter::class);
        $key     = $this->throttleKey();

        // Cek apakah IP sedang terkunci
        if ($limiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->authError    = "Terlalu banyak percobaan. Pendaftaran dikunci selama 10 menit. Silakan coba lagi nanti.";
            $this->attemptsLeft = 0;
            return;
        }

        $this->validate();

        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'role'     => 'applicant',
        ]);

        // Link existing candidate records matching this email
        \App\Models\Candidate::where('email', $user->email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        // Gagal — catat hit (jika berhasil sampai sini berarti akun dibuat, tapi kita catat hit agar membatasi pendaftaran berulang)
        $limiter->hit($key, self::DECAY_SECONDS);

        Auth::login($user);

        session()->flash('message', 'Selamat datang, ' . $user->name . '!');

        return redirect()->route('candidate.dashboard');
    }

    /**
     * Render komponen antarmuka halaman registrasi.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.cw.register-applicant');
    }
}
