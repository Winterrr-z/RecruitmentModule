<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;


/**
 * Class MppIndex
 *
 * Komponen Livewire untuk menampilkan daftar (tabel) seluruh Rencana Tenaga Kerja (MPP).
 * Dilengkapi dengan fitur pencarian, filter berdasarkan departemen, 
 * filter status, dan pengurutan (sorting), serta aksi untuk menghapus data.
 *
 * @package App\Livewire\Mpp
 */
#[Layout('layouts.hr')]
class MppIndex extends Component
{
    use WithPagination;

    /** @var string Kata kunci pencarian. */
    public $search = '';

    /** @var string Departemen terpilih untuk filter. */
    public $selectedDepartment = '';

    /** @var string Status terpilih untuk filter (misal: Draft, Approved, Closed). */
    public $status = '';

    /** @var string Kriteria pengurutan tabel (default: berdasarkan prioritas status). */
    public $sortBy = 'status_priority';

    /**
     * Mereset halaman paginasi ke awal (halaman 1) setiap kali kata kunci pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Mereset halaman paginasi ke awal setiap kali filter departemen berubah.
     */
    public function updatingSelectedDepartment()
    {
        $this->resetPage();
    }

    /**
     * Menghapus Rencana Tenaga Kerja (MPP) yang ditentukan.
     * Akan dicegah (error) jika MPP sudah berstatus 'Closed' atau 'Completed'.
     * 
     * @param int $id ID MPP yang akan dihapus.
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
     * Mengembalikan semua parameter pencarian dan filter ke nilai kosong (default).
     * 
     * @return void
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->selectedDepartment = '';
        $this->status = '';
        $this->sortBy = 'status_priority';
    }

    /**
     * Render komponen Livewire.
     * Mengambil daftar MPP dari repositori menggunakan filter yang sedang aktif, 
     * lalu menampilkannya dalam bentuk tabel dengan sistem paginasi.
     * 
     * @param \App\Repositories\MppRepository $repository Repositori MPP
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

        return view('livewire.mpp.mpp-index', compact('departments', 'mpps'));
    }
}
