<?php

namespace App\Livewire\Cw;

use App\Models\Vacancy;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Class CandidateJobDetail
 *
 * Komponen Livewire untuk menampilkan halaman detail lowongan pekerjaan.
 * Halaman ini dapat diakses oleh publik maupun kandidat yang telah masuk (login),
 * dan berisi formulir pengiriman lamaran (CV & Portofolio).
 *
 * @package App\Livewire\Cw
 */
class CandidateJobDetail extends Component
{
    use WithFileUploads;

    /** @var int ID vacancy */
    public $vacancyId;

    /** @var Vacancy Model vacancy */
    public $vacancy;

    // ==========================================
    // ISIAN FORMULIR LAMARAN
    // ==========================================

    /** @var string Nama lengkap kandidat. */
    public string $name = '';

    /** @var string Alamat email kandidat. */
    public string $email = '';

    /** @var string Nomor telepon kandidat. */
    public string $phone = '';

    /** @var mixed File CV yang diunggah pelamar. */
    public $cv;

    /** @var mixed File Portofolio yang diunggah pelamar (opsional). */
    public $portofolio;

    /** @var bool Mengecek apakah pelamar saat ini masih memiliki lamaran aktif di posisi manapun. */
    public bool $hasActiveApplication = false;

    /** @var bool Mengecek apakah pelamar saat ini sudah berstatus dipekerjakan (Hired). */
    public bool $isHired = false;

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
                $this->isHired = Candidate::where('user_id', $user->id)
                    ->where('status', \App\Enums\CandidateStatus::HIRED)
                    ->exists();

                $this->hasActiveApplication = Candidate::where('user_id', $user->id)
                    ->whereNotIn('status', [\App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::WITHDRAWN, \App\Enums\CandidateStatus::EXPIRED, \App\Enums\CandidateStatus::BLACKLISTED])
                    ->exists();
            }
        }
    }

    /**
     * Proses pengiriman lamaran pekerjaan oleh kandidat.
     * Termasuk memvalidasi unggahan file dan mendelegasikan logika ke CandidateService.
     */
    public function apply()
    {
        // Hanya user logged-in dengan role applicant yang bisa melamar
        if (!auth()->check() || auth()->user()->role !== 'applicant') {
            abort(403, 'Aksi ini tidak diizinkan.');
        }

        if ($this->isHired) {
            session()->flash('error', 'Anda sudah berstatus Hired dan tidak dapat melamar lowongan baru.');
            return;
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

        // Delegasi ke service
        $redirectRoute = app(\App\Services\CandidateService::class)->applyForJob(
            $this->vacancy,
            auth()->id(),
            [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ],
            $this->cv,
            $this->portofolio
        );

        if ($redirectRoute) {
            return redirect($redirectRoute);
        }

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
