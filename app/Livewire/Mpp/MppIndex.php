<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class MppIndex
 * 
 * Komponen Livewire untuk menampilkan daftar Manpower Planning (MPP).
 * Menangani fungsi CRUD termasuk pembuatan, pengeditan (melalui modal),
 * penghapusan, dan pemformatan data seperti gaji dan kalkulasi target waktu.
 *
 * @package App\Livewire
 */
class MppIndex extends Component
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection Daftar semua MPP.
     */
    public $mpps;

    /** @var string Kata kunci pencarian. */
    public $search = '';

    /** @var string Departemen terpilih untuk filter. */
    public $selectedDepartment = '';

    /**
     * Delete the specified Manpower Plan.
     * Menghapus MPP berdasarkan ID dari database.
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $mpp = Mpp::findOrFail($id);
        $status = $mpp->getComputedStatus();
        if ($status === 'Closed' || $status === 'Filled') {
            session()->flash('error', 'Tidak dapat menghapus MPP plan yang sudah closed atau filled.');
            return;
        }
        
        $mpp->delete();

        session()->flash('message', 'Manpower Plan berhasil dihapus.');
    }

    /**
     * Reset search and department filters.
     * 
     * @return void
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->selectedDepartment = '';
    }

    /**
     * Render the Livewire component.
     * Memuat daftar semua MPP dari database berdasarkan filter dan menampilkannya di view index.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $departments = Mpp::select('departemen')
            ->whereNotNull('departemen')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen');

        $query = Mpp::with('lowongans.candidates')->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('nama_plan', 'like', '%' . $this->search . '%')
                  ->orWhere('jabatan', 'like', '%' . $this->search . '%')
                  ->orWhere('departemen', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedDepartment)) {
            $query->where('departemen', $this->selectedDepartment);
        }

        $this->mpps = $query->get();

        return view('livewire.mpp.index', compact('departments'))
            ->layout('layouts.app');
    }
}
