<?php

namespace App\Livewire;

use App\Models\Lowongan;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateJobDetail extends Component
{
    use WithFileUploads;

    /** @var int ID lowongan */
    public $lowonganId;

    /** @var Lowongan Model lowongan */
    public $lowongan;

    // Form properties
    public string $nama = '';
    public string $email = '';
    public string $telepon = '';
    public $cv;
    public $portofolio;

    /**
     * Inisialisasi data komponen.
     */
    public function mount($id): void
    {
        $this->lowonganId = $id;
        $this->lowongan = Lowongan::where('status', 'Published')
            ->findOrFail($id);

        if (auth()->check()) {
            $user = auth()->user();
            $this->nama = $user->name;
            $this->email = $user->email;
        }
    }

    /**
     * Proses lamaran pekerjaan.
     */
    public function apply()
    {
        // Hanya user logged-in dengan role applicant yang bisa melamar
        if (!auth()->check() || auth()->user()->role !== 'applicant') {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        $this->validate([
            'telepon' => ['required', 'string', 'max:20'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:5120'], // max 5MB
            'portofolio' => ['nullable', 'file', 'mimes:pdf', 'max:5120'], // max 5MB
        ], [
            'telepon.required' => 'Nomor telepon wajib diisi.',
            'cv.required' => 'CV PDF wajib diunggah.',
            'cv.mimes' => 'CV harus berformat PDF.',
            'cv.max' => 'Ukuran CV maksimal 5MB.',
            'portofolio.mimes' => 'Portofolio harus berformat PDF.',
            'portofolio.max' => 'Ukuran portofolio maksimal 5MB.',
        ]);

        // Cek blacklist
        $isBlacklisted = DB::table('blacklist')
            ->where('email', $this->email)
            ->orWhere('telepon', $this->telepon)
            ->exists();

        if ($isBlacklisted) {
            return redirect()->route('blacklist.info');
        }

        // Upload file ke storage/app/private/candidates (disk local root adalah storage/app/private)
        $cvPath = $this->cv->store('candidates', 'local');
        $portofolioPath = $this->portofolio 
            ? $this->portofolio->store('candidates', 'local')
            : null;

        // Simpan kandidat
        Candidate::create([
            'lowongan_id' => $this->lowongan->id,
            'user_id' => auth()->id(),
            'nama' => $this->nama,
            'email' => $this->email,
            'telepon' => $this->telepon,
            'cv_path' => $cvPath,
            'portofolio_path' => $portofolioPath,
            'current_stage_id' => 1, // Applied
            'status' => 'Applied',
            'source' => 'public',
        ]);

        session()->flash('message', 'Lamaran berhasil dikirim!');

        return redirect()->route('candidate.dashboard');
    }

    /**
     * Render komponen dengan layout sesuai status login.
     */
    public function render()
    {
        $layout = auth()->check() ? 'layouts.applicant' : 'layouts.guest';
        return view('livewire.candidate-job-detail')
            ->layout($layout);
    }
}
