<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Vacancy;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

/**
 * Class AtsAllCandidates
 *
 * Komponen Livewire untuk menampilkan daftar seluruh kandidat di sistem ATS.
 * Mendukung pencarian, filter berdasarkan lowongan, status, dan tahapan (stage).
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsAllCandidates extends Component
{
    use WithPagination;

    /** @var string Filter berdasarkan ID Lowongan. */
    public $filterVacancy = '';

    /** @var string Filter berdasarkan status kandidat (misal: Active, Rejected). */
    public $filterStatus = '';

    /** @var string Filter berdasarkan ID Tahapan (Stage). */
    public $filterStage = '';

    /** @var string Kata kunci pencarian nama kandidat. */
    public $search = '';

    protected $paginationTheme = 'tailwind';

    /**
     * Dijalankan otomatis ketika ada ketikan di kolom pencarian.
     * Mereset paginasi kembali ke halaman pertama.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Dijalankan otomatis ketika filter lowongan diubah.
     */
    public function updatingFilterVacancy()
    {
        $this->resetPage();
    }

    /**
     * Dijalankan otomatis ketika filter status diubah.
     */
    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    /**
     * Dijalankan otomatis ketika filter tahapan diubah.
     */
    public function updatingFilterStage()
    {
        $this->resetPage();
    }

    /**
     * Render komponen antarmuka.
     * Mengambil daftar kandidat yang telah disaring menggunakan CandidateRepository.
     */
    public function render()
    {
        $vacancies = Vacancy::all();
        $stages = Stage::getAllCached();

        $candidates = app(\App\Repositories\CandidateRepository::class)->getAllCandidates(
            $this->filterVacancy,
            $this->filterStatus,
            $this->filterStage,
            $this->search,
            15
        );

        return view('livewire.ats.ats-all-candidates', [
            'vacancies' => $vacancies,
            'stages' => $stages,
            'candidates' => $candidates,
        ]);
    }
}
