<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Lowongan;
use Livewire\Component;
use Livewire\WithFileUploads;

class AtsManualCandidate extends Component
{
    use WithFileUploads;

    public $lowonganId;
    public $lowongan;

    // Form fields
    public $nama = '';
    public $email = '';
    public $telepon = '';
    public $cv;
    public $portofolio;

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telepon' => 'required|string|max:20',
            'cv' => 'required|file|mimes:pdf|max:5120', // max 5MB
            'portofolio' => 'nullable|file|mimes:pdf|max:5120', // max 5MB
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama lengkap wajib diisi.',
        'nama.max' => 'Nama lengkap maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'telepon.required' => 'Nomor telepon wajib diisi.',
        'telepon.max' => 'Nomor telepon maksimal 20 karakter.',
        'cv.required' => 'File CV wajib diunggah.',
        'cv.mimes' => 'CV harus berupa file PDF.',
        'cv.max' => 'Ukuran CV maksimal 5MB.',
        'portofolio.mimes' => 'Portofolio harus berupa file PDF.',
        'portofolio.max' => 'Ukuran portofolio maksimal 5MB.',
    ];

    public function mount($lowonganId = null)
    {
        $this->lowonganId = $lowonganId;
        $this->lowongan = $lowonganId ? Lowongan::find($lowonganId) : null;
    }

    public function save()
    {
        $this->validate();

        // Upload files
        $cvPath = $this->cv->store('cvs', 'local');
        $portofolioPath = $this->portofolio ? $this->portofolio->store('portofolios', 'local') : null;

        // Save candidate
        Candidate::create([
            'lowongan_id' => $this->lowonganId ?: null,
            'nama' => $this->nama,
            'email' => $this->email,
            'telepon' => $this->telepon,
            'cv_path' => $cvPath,
            'portofolio_path' => $portofolioPath,
            'current_stage_id' => 1, // Applied
            'status' => \App\Enums\CandidateStatus::APPLIED,
            'source' => 'manual',
        ]);

        session()->flash('message', "Kandidat manual '{$this->nama}' berhasil ditambahkan.");

        return redirect()->route('ats.dashboard', ['selectedLowonganId' => $this->lowonganId ?: null]);
    }

    public function render()
    {
        return view('livewire.ats.manual-candidate')->layout('layouts.app');
    }
}
