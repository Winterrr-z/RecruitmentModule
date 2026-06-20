<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class MppDetail
 * 
 * Komponen Livewire untuk menampilkan detail spesifik dari sebuah rencana tenaga kerja (Manpower Planning / MPP).
 * Menangani logika persetujuan (approval), penutupan manual, dan pengecekan relasi dengan lowongan.
 *
 * @package App\Livewire\Mpp
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
     * Inisialisasi komponen.
     * Menerima parameter ID MPP dan memuat data terkait.
     *
     * @param int|null $id
     * @param int|null $mppId
     * @return void
     */
    public function mount($id = null, $mppId = null)
    {
        $this->mppId = $id ?? $mppId;
        $this->loadMpp();
    }

    /**
     * Mengambil data MPP dari database beserta relasi Recruitment Request (RR).
     * Melakukan perhitungan sisa kuota dan mengecek status RR.
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
     * Menyetujui (Approve) Manpower Planning melalui MppService.
     * Akan mengubah status MPP dari 'Draft' menjadi 'Approved'.
     * 
     * @param \App\Services\MppService $service Layanan MPP.
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
     * Menutup paksa Manpower Planning melalui MppService.
     * Mengubah status MPP menjadi 'Closed' jika belum sepenuhnya terpenuhi 
     * namun HR memutuskan untuk menghentikan pencarian.
     * 
     * @param \App\Services\MppService $service Layanan MPP.
     * @return void
     */
    public function closePlan(\App\Services\MppService $service)
    {
        if ($service->close($this->mpp)) {
            session()->flash('message', 'Manpower Planning berhasil ditutup.');
            $this->loadMpp();
        } else {
            session()->flash('error', 'Tidak dapat menutup plan. Pastikan plan berstatus Draft/Approved dan tidak ada Recruitment Request atau kandidat yang aktif.');
        }
    }

    /**
     * Render komponen antarmuka halaman detail MPP.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->loadMpp();

        return view('livewire.mpp.mpp-detail');
    }
}
