<?php

namespace App\Livewire\Cw;

use App\Models\Lowongan;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class CareerJobList
 *
 * Komponen Livewire untuk menampilkan daftar lowongan pekerjaan publik maupun
 * untuk pelamar yang sudah login.
 *
 * - Guest     → layouts.guest + view career-job-list (top-bar filter)
 * - Auth      → layouts.applicant + view career-job-list-logged-in (sidebar filter)
 *
 * @package App\Livewire
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
     * Render komponen.
     *
     * Mendeteksi status autentikasi dan menyajikan view serta layout yang sesuai.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $isLoggedIn = auth()->check();

        // --- Base query ---
        $query = Lowongan::query()
            ->where('status', 'Published')
            ->where('kuota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today());

        // --- Search (shared) ---
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('jabatan', 'like', '%' . $this->search . '%')
                  ->orWhere('departemen', 'like', '%' . $this->search . '%');
            });
        }

        if ($isLoggedIn) {
            // --- Sidebar checkbox filters (logged-in) ---
            if (!empty($this->selectedDepartments)) {
                $query->whereIn('departemen', $this->selectedDepartments);
            }
            if (!empty($this->selectedTypes)) {
                $query->whereIn('tipe_kerja', $this->selectedTypes);
            }
        } else {
            // --- Dropdown filters (guest) ---
            if (!empty($this->selectedTipeKerja)) {
                $query->where('tipe_kerja', $this->selectedTipeKerja);
            }
            if (!empty($this->selectedLokasi)) {
                $query->where('lokasi', $this->selectedLokasi);
            }
        }

        // --- Sorting ---
        $direction = $this->sortBy === 'oldest' ? 'asc' : 'desc';
        $lowongans = $query->orderBy('created_at', $direction)->get();

        // --- Department list with counts (for sidebar) ---
        $departments = [];
        if ($isLoggedIn) {
            $departments = Lowongan::query()
                ->where('status', 'Published')
                ->where('kuota', '>', 0)
                ->where('application_deadline', '>=', Carbon::today())
                ->selectRaw('departemen, count(*) as total')
                ->groupBy('departemen')
                ->orderBy('departemen')
                ->pluck('total', 'departemen')
                ->toArray();
        }

        if ($isLoggedIn) {
            return view('livewire.cw.career-job-list-logged-in', compact('lowongans', 'departments'))
                ->layout('layouts.applicant');
        }

        return view('livewire.cw.career-job-list', compact('lowongans'))
            ->layout('layouts.guest');
    }
}
