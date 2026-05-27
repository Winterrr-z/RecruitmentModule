<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use App\Models\Lowongan;
use Livewire\Component;

/**
 * Class MppDetail
 * 
 * Komponen Livewire untuk menampilkan detail spesifik dari sebuah Manpower Planning (MPP).
 * Menangani logika persetujuan (approval) dan pengecekan relasi dengan lowongan.
 *
 * @package App\Livewire
 */
class MppDetail extends Component
{
    /**
     * @var int ID dari Manpower Planning yang sedang dilihat.
     */
    public $mppId;

    /**
     * @var Mpp|null Instance model Manpower Planning.
     */
    public $mpp;

    /**
     * @var bool Status apakah MPP ini sudah memiliki data lowongan yang berelasi.
     */
    public $hasLowongan = false;

    /**
     * @var int Sisa kuota MPP yang belum terpenuhi.
     */
    public $remainingQuota = 0;

    /**
     * @var bool Apakah MPP memiliki Lowongan/Recruitment Request yang aktif (belum completed/closed).
     */
    public $hasActiveRr = false;

    /**
     * @var \Illuminate\Database\Eloquent\Collection Daftar Lowongan terkait.
     */
    public $mppLowongans;

    /**
     * Initialize the component.
     * Menerima parameter mppId dan memuat data terkait.
     *
     * @param int $mppId
     * @return void
     */
    public function mount($id = null, $mppId = null)
    {
        $this->mppId = $id ?? $mppId;
        $this->loadMpp();
    }

    /**
     * Load the MPP and check relationship status.
     * Mengambil data Mpp berdasarkan ID dan mengecek status lowongan, sisa kuota, serta relasi lowongan.
     * 
     * @return void
     */
    protected function loadMpp()
    {
        $this->mpp = Mpp::with('lowongans')->findOrFail($this->mppId);
        $this->mppLowongans = $this->mpp->lowongans;
        $this->hasLowongan = $this->mppLowongans->isNotEmpty();

        // Cari sisa kuota
        $hiredCount = \App\Models\Candidate::whereHas('lowongan', function ($query) {
            $query->where('mpp_id', $this->mpp->id);
        })->where('status', 'Hired')->count();

        $this->remainingQuota = max(0, $this->mpp->jumlah_kebutuhan - $hiredCount);

        // Cari apakah ada Lowongan di bawah MPP ini yang tidak berstatus Completed/Closed
        $this->hasActiveRr = $this->mppLowongans->contains(function ($lowongan) {
            return $lowongan->status !== 'Completed/Closed';
        });
    }

    /**
     * Approve the Manpower Planning.
     * Mengubah status MPP dari 'draft' menjadi 'approved' dan menampilkan pesan flash.
     * 
     * @return void
     */
    public function approve()
    {
        if (strtolower($this->mpp->status) === 'draft') {
            $this->mpp->update(['status' => 'approved']);
            session()->flash('message', 'Manpower Planning berhasil disetujui.');
            $this->loadMpp(); // refresh data
        }
    }

    /**
     * Render the component view.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->loadMpp();

        return view('livewire.mpp.mpp-detail')
            ->layout('layouts.app');
    }
}
