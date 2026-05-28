<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

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
    use WithPagination;

    /** @var string Kata kunci pencarian. */
    public $search = '';

    /** @var string Departemen terpilih untuk filter. */
    public $selectedDepartment = '';

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

        $query = Mpp::with('lowongans.candidates')
            ->select('mpps.*')
            ->selectSub(function ($q) {
                $q->selectRaw('count(*)')
                  ->from('candidates')
                  ->join('lowongans', 'lowongans.id', '=', 'candidates.lowongan_id')
                  ->join('recruitment_requests', 'recruitment_requests.id', '=', 'lowongans.recruitment_request_id')
                  ->whereColumn('recruitment_requests.mpp_id', 'mpps.id')
                  ->where('candidates.status', 'Hired');
            }, 'hired_count');

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

        $mpps = $query->orderByRaw("CASE WHEN lower(status) = 'closed' OR hired_count >= jumlah_kebutuhan THEN 1 ELSE 0 END ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.mpp.index', compact('departments', 'mpps'))
            ->layout('layouts.app');
    }
}
