<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Vacancy;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

/**
 * Class AtsManualCandidate
 *
 * Komponen Livewire untuk menambahkan data kandidat secara manual oleh HR.
 * Digunakan jika lamaran masuk di luar portal rekrutmen (misal: referal, email langsung).
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsManualCandidate extends Component
{
    use WithFileUploads;

    /** @var int|null ID Lowongan yang dipilih (bisa kosong jika melamar mandiri tanpa loker). */
    public $vacancyId;

    /** @var \App\Models\Vacancy|null Objek lowongan berdasarkan $vacancyId. */
    public $vacancy;

    // ==========================================
    // ISIAN FORMULIR
    // ==========================================

    /** @var string Nama lengkap kandidat. */
    public $name = '';

    /** @var string Email kandidat. */
    public $email = '';

    /** @var string Nomor telepon kandidat. */
    public $phone = '';

    /** @var mixed File CV yang diunggah. */
    public $cv;

    /** @var mixed File Portofolio yang diunggah (opsional). */
    public $portofolio;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'cv' => 'required|file|mimetypes:application/pdf|mimes:pdf|max:5120', // max 5MB
            'portofolio' => 'nullable|file|mimetypes:application/pdf|mimes:pdf|max:5120', // max 5MB
            'vacancyId' => 'nullable|exists:vacancies,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.max' => 'Nama lengkap maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'phone.required' => 'Nomor telepon wajib diisi.',
        'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        'cv.required' => 'File CV wajib diunggah.',
        'cv.mimes' => 'CV harus berupa file PDF.',
        'cv.max' => 'Ukuran CV maksimal 5MB.',
        'portofolio.mimes' => 'Portofolio harus berupa file PDF.',
        'portofolio.max' => 'Ukuran portofolio maksimal 5MB.',
        'vacancyId.exists' => 'Lowongan kerja yang dipilih tidak valid.',
    ];

    /**
     * Inisialisasi awal. Jika ID lowongan dikirim lewat URL, langsung pilih lowongan tersebut.
     *
     * @param int|null $vacancyId
     */
    public function mount($vacancyId = null)
    {
        $this->vacancyId = $vacancyId;
        $this->vacancy = $vacancyId ? Vacancy::find($vacancyId) : null;
    }

    /**
     * Dijalankan otomatis saat $vacancyId berubah. Mengambil data model Vacancy.
     *
     * @param int|string|null $value
     */
    public function updatedVacancyId($value)
    {
        $this->vacancy = $value ? Vacancy::find($value) : null;
    }

    /**
     * Simpan data kandidat manual ke database dan unggah (upload) filenya ke media penyimpanan lokal.
     */
    public function save()
    {
        $this->validate();

        // Upload files
        $cvPath = $this->cv->store('cvs', 'local');
        $portofolioPath = $this->portofolio ? $this->portofolio->store('portofolios', 'local') : null;

        // Save candidate
        Candidate::create([
            'vacancy_id' => $this->vacancyId ?: null,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cv_path' => $cvPath,
            'portofolio_path' => $portofolioPath,
            'current_stage_id' => 1, // Applied
            'status' => \App\Enums\CandidateStatus::APPLIED,
            'source' => 'manual',
        ]);

        session()->flash('message', "Kandidat manual '{$this->name}' berhasil ditambahkan.");

        return redirect()->route('ats.dashboard', ['selectedVacancyId' => $this->vacancyId ?: null]);
    }

    /**
     * Render antarmuka form penambahan kandidat manual.
     */
    public function render()
    {
        $vacancies = Vacancy::all();
        return view('livewire.ats.manual-candidate', [
            'vacancies' => $vacancies,
        ]);
    }
}
