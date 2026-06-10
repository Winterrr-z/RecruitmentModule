<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Vacancy;
use Livewire\Component;
use Livewire\WithFileUploads;

class AtsManualCandidate extends Component
{
    use WithFileUploads;

    public $vacancyId;
    public $vacancy;

    // Form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $cv;
    public $portofolio;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'cv' => 'required|file|mimetypes:application/pdf|mimes:pdf|max:5120', // max 5MB
            'portofolio' => 'nullable|file|mimetypes:application/pdf|mimes:pdf|max:5120', // max 5MB
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
    ];

    public function mount($vacancyId = null)
    {
        $this->vacancyId = $vacancyId;
        $this->vacancy = $vacancyId ? Vacancy::find($vacancyId) : null;
    }

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

    public function render()
    {
        return view('livewire.ats.manual-candidate')->layout('layouts.hr');
    }
}
