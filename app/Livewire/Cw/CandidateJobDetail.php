<?php

namespace App\Livewire\Cw;

use App\Models\Vacancy;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CandidateJobDetail extends Component
{
    use WithFileUploads;

    /** @var int ID vacancy */
    public $vacancyId;

    /** @var Vacancy Model vacancy */
    public $vacancy;

    // Form properties
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $cv;
    public $portofolio;

    public bool $hasActiveApplication = false;

    /**
     * Inisialisasi data komponen.
     */
    public function mount($id): void
    {
        $this->vacancyId = $id;
        $this->vacancy = Vacancy::where('status', 'Published')
            ->findOrFail($id);

        if (auth()->check()) {
            $user = auth()->user();
            $this->name = $user->name;
            $this->email = $user->email;
            
            if ($user->role === 'applicant') {
                $this->hasActiveApplication = Candidate::where('user_id', $user->id)
                    ->whereNotIn('status', [\App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::DECLINED, \App\Enums\CandidateStatus::EXPIRED, \App\Enums\CandidateStatus::BLACKLISTED])
                    ->exists();
            }
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

        if ($this->hasActiveApplication) {
            session()->flash('error', 'Anda masih memiliki lamaran aktif. Selesaikan proses seleksi tersebut terlebih dahulu.');
            return;
        }

        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
            'cv' => ['required', 'file', 'mimetypes:application/pdf', 'mimes:pdf', 'max:5120'], // max 5MB
            'portofolio' => ['nullable', 'file', 'mimetypes:application/pdf', 'mimes:pdf', 'max:5120'], // max 5MB
        ], [
            'phone.required' => 'Nomor telepon wajib diisi.',
            'cv.required' => 'CV PDF wajib diunggah.',
            'cv.mimes' => 'CV harus berformat PDF.',
            'cv.max' => 'Ukuran CV maksimal 5MB.',
            'portofolio.mimes' => 'Portofolio harus berformat PDF.',
            'portofolio.max' => 'Ukuran portofolio maksimal 5MB.',
        ]);

        // Cek blacklist
        $isBlacklisted = DB::table('blacklist')
            ->where('email', $this->email)
            ->orWhere('phone', $this->phone)
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
            'vacancy_id' => $this->vacancy->id,
            'user_id' => auth()->id(),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cv_path' => $cvPath,
            'portofolio_path' => $portofolioPath,
            'current_stage_id' => 1, // Applied
            'status' => \App\Enums\CandidateStatus::APPLIED,
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
        return view('livewire.cw.candidate-job-detail')
            ->layout($layout);
    }
}
