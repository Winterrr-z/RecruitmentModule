<?php

namespace App\Livewire;

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
     * Initialize the component.
     * Menerima parameter mppId dan memuat data terkait.
     *
     * @param int $mppId
     * @return void
     */
    public function mount($mppId)
    {
        $this->mppId = $mppId;
        $this->loadMpp();
    }

    /**
     * Load the MPP and check relationship status.
     * Mengambil data Mpp berdasarkan ID dan mengecek apakah ada record Lowongan dengan mpp_id yang sama.
     * 
     * @return void
     */
    protected function loadMpp()
    {
        $this->mpp = Mpp::findOrFail($this->mppId);
        $this->hasLowongan = Lowongan::where('mpp_id', $this->mpp->id)->exists();
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
