<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class MppDetail
 * 
 * Komponen Livewire untuk menampilkan detail spesifik dari sebuah Manpower Planning (MPP).
 * Menangani logika persetujuan (approval) dan pengecekan relasi dengan vacancy.
 *
 * @package App\Livewire
 */
#[Layout('layouts.hr')]
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
     * @var bool Status apakah MPP ini sudah memiliki data vacancy yang berelasi.
     */
    public $hasVacancy = false;

    /**
     * @var int Sisa kuota MPP yang belum terpenuhi.
     */
    public $remainingQuota = 0;

    /**
     * @var bool Apakah MPP memiliki Vacancy/Recruitment Request yang aktif (belum completed/closed).
     */
    public $hasActiveRr = false;

    /**
     * @var \Illuminate\Database\Eloquent\Collection Daftar Vacancy terkait.
     */
    public $mppVacancies;

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
     * Mengambil data Mpp berdasarkan ID dan mengecek status vacancy, sisa kuota, serta relasi vacancy.
     * 
     * @return void
     */
    protected function loadMpp()
    {
        $this->mpp = Mpp::with('rrs')->findOrFail($this->mppId);
        $this->mppVacancies = $this->mpp->rrs;
        $this->hasVacancy = $this->mppVacancies->isNotEmpty();

        // Cari sisa kuota
        $this->remainingQuota = $this->mpp->sisaKuota();

        // Cari apakah ada RR di bawah MPP ini yang berstatus aktif (Ready to Publish / Published)
        $this->hasActiveRr = $this->mppVacancies->contains(function ($rr) {
            return in_array($rr->status->value ?? $rr->status, ['Ready to Publish', 'Published']);
        });
    }

    /**
     * Approve the Manpower Planning.
     * Mengubah status MPP dari 'draft' menjadi 'approved' dan menampilkan pesan flash.
     * 
     * @return void
     */
    public function approve(\App\Services\MppService $service)
    {
        if ($service->approve($this->mpp)) {
            session()->flash('message', 'Manpower Planning berhasil disetujui.');
            $this->loadMpp();
        }
    }

    /**
     * Tutup Manpower Planning.
     * Mengubah status MPP menjadi 'Closed' jika syarat terpenuhi.
     * 
     * @return void
     */
    public function closePlan(\App\Services\MppService $service)
    {
        if ($service->close($this->mpp)) {
            session()->flash('message', 'Manpower Planning berhasil ditutup.');
            $this->loadMpp();
        } else {
            session()->flash('error', 'Tidak dapat menutup plan. Pastikan plan sudah di-approve dan tidak ada Recruitment Request yang aktif.');
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

        return view('livewire.mpp.mpp-detail');
    }
}
