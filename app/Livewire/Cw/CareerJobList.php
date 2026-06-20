<?php

namespace App\Livewire\Cw;

use App\Models\Vacancy;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class CareerJobList
 *
 * Komponen Livewire untuk menampilkan daftar lowongan pekerjaan (vacancy)
 * baik secara publik (belum login) maupun untuk pelamar yang sudah masuk (login).
 *
 * - Belum login (Guest) → menggunakan layout 'layouts.guest' dengan filter di baris atas.
 * - Sudah login (Auth)  → menggunakan layout 'layouts.applicant' dengan filter di bilah sisi (sidebar).
 *
 * @package App\Livewire\Cw
 */
class CareerJobList extends Component
{
    // --- Shared ---

    /** @var string Kata kunci pencarian (jabatan / departemen). */
    public string $search = '';

    // --- Guest filters ---

    /** @var string Filter tipe kerja untuk guest dropdown. */
    public string $selectedTipeKerja = '';

    /** @var string Filter lokasi untuk guest dropdown. */
    public string $selectedLokasi = '';

    // --- Logged-in (sidebar) filters ---

    /** @var array<string> Daftar departemen yang dipilih (checkbox). */
    public array $selectedDepartments = [];

    /** @var array<string> Daftar tipe kerja yang dipilih (checkbox). */
    public array $selectedTypes = [];

    /** @var string Urutan tampilan: 'newest' | 'oldest'. */
    public string $sortBy = 'newest';

    // -------------------------------------------------------------------------

    /**
     * Reset semua filter ke nilai default.
     *
     * @return void
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedTipeKerja',
            'selectedLokasi',
            'selectedDepartments',
            'selectedTypes',
            'sortBy',
        ]);
    }

    /**
     * Render komponen antarmuka.
     * Mendeteksi status masuk (login) pengguna dan menyajikan tampilan serta layout yang sesuai.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $isLoggedIn = auth()->check();

        // --- Base query ---
        $query = Vacancy::query()
            ->where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today());

        // --- Search (shared) ---
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('job_title', 'like', '%' . $this->search . '%')
                  ->orWhere('department', 'like', '%' . $this->search . '%');
            });
        }

        if ($isLoggedIn) {
            // --- Sidebar checkbox filters (logged-in) ---
            if (!empty($this->selectedDepartments)) {
                $query->whereIn('department', $this->selectedDepartments);
            }
            if (!empty($this->selectedTypes)) {
                $query->whereIn('employment_type', $this->selectedTypes);
            }
        } else {
            // --- Dropdown filters (guest) ---
            if (!empty($this->selectedTipeKerja)) {
                $query->where('employment_type', $this->selectedTipeKerja);
            }
            if (!empty($this->selectedLokasi)) {
                $query->where('location', $this->selectedLokasi);
            }
        }

        // --- Sorting ---
        $direction = $this->sortBy === 'oldest' ? 'asc' : 'desc';
        $vacancies = $query->orderBy('created_at', $direction)->paginate(10);

        // --- Department list with counts (for sidebar) ---
        $departments = [];
        if ($isLoggedIn) {
            $departments = \Illuminate\Support\Facades\Cache::remember('vacancy_department_counts', 3600, function () {
                return Vacancy::query()
                    ->where('status', 'Published')
                    ->where('quota', '>', 0)
                    ->where('application_deadline', '>=', Carbon::today())
                    ->selectRaw('department, count(*) as total')
                    ->groupBy('department')
                    ->orderBy('department')
                    ->pluck('total', 'department')
                    ->toArray();
            });
        }

        if ($isLoggedIn) {
            return view('livewire.cw.career-job-list-logged-in', compact('vacancies', 'departments'))
                ->layout('layouts.applicant');
        }

        return view('livewire.cw.career-job-list', compact('vacancies'))
            ->layout('layouts.guest');
    }
}
