<?php

namespace App\Livewire\Cw;

use App\Models\Lowongan;
use Carbon\Carbon;
use Livewire\Component;

class CandidateJobList extends Component
{
    /** @var string Kata kunci pencarian. */
    public string $search = '';

    /** @var array<string> Filter departemen (checkbox). */
    public array $selectedDepartments = [];

    /** @var array<string> Filter tipe kerja (checkbox). */
    public array $selectedTypes = [];

    /** @var string Urutan tampilan: 'newest' | 'oldest'. */
    public string $sortBy = 'newest';

    /**
     * Reset semua filter ke default.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedDepartments',
            'selectedTypes',
            'sortBy',
        ]);
    }

    /**
     * Render daftar lowongan khusus pelamar login.
     */
    public function render()
    {
        $query = Lowongan::query()
            ->where('status', 'Published')
            ->where('kuota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('jabatan', 'like', '%' . $this->search . '%')
                  ->orWhere('departemen', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedDepartments)) {
            $query->whereIn('departemen', $this->selectedDepartments);
        }

        if (!empty($this->selectedTypes)) {
            $query->whereIn('tipe_kerja', $this->selectedTypes);
        }

        $direction = $this->sortBy === 'oldest' ? 'asc' : 'desc';
        $lowongans = $query->orderBy('created_at', $direction)->get();

        // Rekap jumlah per departemen untuk sidebar
        $departments = Lowongan::query()
            ->where('status', 'Published')
            ->where('kuota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today())
            ->selectRaw('departemen, count(*) as total')
            ->groupBy('departemen')
            ->orderBy('departemen')
            ->pluck('total', 'departemen')
            ->toArray();

        return view('livewire.cw.career-job-list-logged-in', compact('lowongans', 'departments'))
            ->layout('layouts.applicant');
    }
}
