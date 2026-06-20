<?php

namespace App\Livewire\Cw;

use App\Models\Vacancy;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class PublicJobList
 *
 * Komponen Livewire untuk menampilkan daftar lowongan pekerjaan (vacancy)
 * bagi pengunjung umum (belum login/guest). 
 *
 * @package App\Livewire\Cw
 */
#[Layout('layouts.guest')]
class PublicJobList extends Component
{
    /** @var string Kata kunci pencarian. */
    public string $search = '';

    /** @var string Filter tipe kerja. */
    public string $selectedTipeKerja = '';

    /** @var string Filter lokasi. */
    public string $selectedLokasi = '';

    /**
     * Reset semua filter ke default.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedTipeKerja',
            'selectedLokasi',
        ]);
    }

    /**
     * Render daftar lowongan untuk publik.
     * Memfilter berdasarkan lowongan yang 'Published', kuota tersedia, dan belum melewati batas waktu lamaran.
     */
    public function render()
    {
        $query = Vacancy::query()
            ->where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('job_title', 'like', '%' . $this->search . '%')
                  ->orWhere('department', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedTipeKerja)) {
            $query->where('employment_type', $this->selectedTipeKerja);
        }

        if (!empty($this->selectedLokasi)) {
            $query->where('location', $this->selectedLokasi);
        }

        $vacancies = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.cw.career-job-list', compact('vacancies'));
    }
}
