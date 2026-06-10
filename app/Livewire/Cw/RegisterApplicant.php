<?php

namespace App\Livewire\Cw;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class RegisterApplicant
 *
 * Form registrasi untuk pelamar baru.
 * Setelah berhasil mendaftar, user langsung di-login
 * dan diarahkan ke dashboard kandidat.
 *
 * @package App\Livewire
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

    /**
     * Aturan validasi form registrasi.
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
     * Pesan validasi kustom.
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
     * Proses registrasi pelamar baru.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register()
    {
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

        Auth::login($user);

        session()->flash('message', 'Selamat datang, ' . $user->name . '!');

        return redirect()->route('candidate.dashboard');
    }

    /**
     * Render komponen.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.cw.register-applicant');
    }
}
