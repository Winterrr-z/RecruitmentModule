<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;


/**
 * Class MppIndex
 *
 * Komponen Livewire untuk menampilkan daftar Manpower Planning (MPP).
 * Menangani fungsi CRUD termasuk pembuatan, pengeditan (melalui modal),
 * penghapusan, dan pemformatan data seperti gaji dan kalkulasi target waktu.
 *
 * @package App\Livewire
 */
#[Layout('layouts.app')]
class MppIndex extends Component
{
    use WithPagination;

    /** @var string Kata kunci pencarian. */
    public $search = '';

    /** @var string Departemen terpilih untuk filter. */
    public $selectedDepartment = '';

    public $status = '';
    public $sortBy = 'newest';

    /**
     * Reset pagination page when search keyword is updated.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Reset pagination page when selected department filter is updated.
     */
    public function updatingSelectedDepartment()
    {
        $this->resetPage();
    }

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
        if ($status === 'Closed' || $status === 'Completed') {
            session()->flash('error', 'Tidak dapat menghapus MPP plan yang sudah closed atau completed.');
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
        $this->status = '';
        $this->sortBy = 'newest';
    }

    /**
     * Render the Livewire component.
     * Memuat daftar semua MPP dari database berdasarkan filter dan menampilkannya di view index.
     * 
     * @return \Illuminate\View\View
     */
    public function render(\App\Repositories\MppRepository $repository)
    {
        $departments = $repository->getUniqueDepartments();

        $filters = [
            'search' => $this->search,
            'department' => $this->selectedDepartment,
            'status' => $this->status,
            'sortBy' => $this->sortBy,
        ];

        $mpps = $repository->getPaginatedList($filters, 12);

        return view('livewire.mpp.index', compact('departments', 'mpps'));
    }
}
