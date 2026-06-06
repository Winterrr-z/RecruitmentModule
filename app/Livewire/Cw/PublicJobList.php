<?php

namespace App\Livewire\Cw;

use App\Models\Lowongan;
use Carbon\Carbon;
use Livewire\Component;

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
     * Render daftar lowongan publik.
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

        if (!empty($this->selectedTipeKerja)) {
            $query->where('tipe_kerja', $this->selectedTipeKerja);
        }

        if (!empty($this->selectedLokasi)) {
            $query->where('lokasi', $this->selectedLokasi);
        }

        $lowongans = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.cw.career-job-list', compact('lowongans'))
            ->layout('layouts.guest');
    }
}
