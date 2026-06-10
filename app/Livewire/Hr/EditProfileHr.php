<?php

namespace App\Livewire\Hr;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

/**
 * Class EditProfileHr
 *
 * Komponen form untuk mengedit profil pengguna HR yang sedang masuk.
 *
 * @package App\Livewire\Hr
 */
#[Layout('layouts.hr')]
class EditProfileHr extends Component
{
    use WithFileUploads;
    /** @var mixed File foto profil. */
    public $photo;

    /** @var string Nama lengkap. */
    public string $name = '';

    /** @var string Email. */
    public string $email = '';

    /** @var string Departemen. */
    public string $department = '';

    /** @var string Jabatan / Job Title. */
    public string $job_title = '';

    /** @var string Nomor telepon. */
    public string $phone_number = '';

    /**
     * Inisialisasi data profil saat komponen dipasang.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->department = $user->department ?? '';
        $this->job_title = $user->job_title ?? '';
        $this->phone_number = $user->phone_number ?? '';
    }

    /**
     * Aturan validasi input profil.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'department'   => ['nullable', 'string', 'max:255'],
            'job_title'    => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:25'],
            'photo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:10240'], // max 10MB
        ];
    }

    /**
     * Pesan kesalahan validasi kustom.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah digunakan oleh pengguna lain.',
            'photo.image'        => 'File yang diunggah harus berupa gambar.',
            'photo.mimes'        => 'Format gambar harus berupa PNG, JPG, atau JPEG.',
            'photo.max'          => 'Ukuran maksimal foto profil adalah 10MB.',
        ];
    }

    /**
     * Simpan perubahan profil pengguna.
     */
    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        
        $data = [
            'name'         => $this->name,
            'email'        => $this->email,
            'department'   => $this->department ?: null,
            'job_title'    => $this->job_title ?: null,
            'phone_number' => $this->phone_number ?: null,
        ];

        if ($this->photo) {
            // Delete old photo if it exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $data['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
        }

        $user->update($data);

        session()->flash('success', 'Profil Anda berhasil diperbarui.');

        $this->redirect(route('hr.profile'), navigate: true);
    }

    /**
     * Render komponen dengan layout HR (layouts.app).
     */
    public function render()
    {
        return view('livewire.hr.edit-profile-hr');
    }
}
