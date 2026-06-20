<?php

namespace App\Livewire\Rr;

use App\Models\Rr;
use App\Services\RrService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

/**
 * Class RRIndex
 * 
 * Komponen Livewire untuk menampilkan daftar Permintaan Rekrutmen (Recruitment Request / RR).
 * Menangani fungsi pencarian, penyaringan (filter) berdasarkan status, pembagian halaman (pagination), 
 * serta aksi untuk memublikasikan (publish) dan menutup (close) RR.
 *
 * @package App\Livewire\Rr
 */
#[Layout('layouts.hr')]
class RRIndex extends Component
{
    use WithPagination;

    /** @var string Query pencarian jabatan atau departemen. Tersimpan di URL. */
    #[Url]
    public $search = '';

    /** @var string Filter status RR (misal: Draft, Published). Tersimpan di URL. */
    #[Url]
    public $status = '';

    /** @var string Filter kriteria pengurutan tabel (contoh: 'newest', 'oldest'). Tersimpan di URL. */
    #[Url]
    public $sortBy = 'newest';

    /**
     * Otomatis dipanggil oleh Livewire sebelum sebuah properti diperbarui.
     * Digunakan untuk mereset halaman paginasi kembali ke halaman 1 
     * ketika pencarian atau filter diubah.
     *
     * @param string $property Nama properti yang sedang diubah.
     * @param mixed $value Nilai baru properti tersebut.
     * @return void
     */
    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'status', 'sortBy'])) {
            $this->resetPage();
        }
    }

    /**
     * Memublikasikan RR (mengubah status dari 'Draft' ke 'Published').
     * Aksi ini otomatis akan membuatkan entri Lowongan (Vacancy) agar bisa diakses oleh publik.
     *
     * @param int $id ID Recruitment Request.
     * @param RrService $service Layanan bisnis RR.
     * @return void
     */
    public function publish($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->publish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan dan Vacancy telah dibuat.');
    }

    /**
     * Menutup RR (mengubah status menjadi 'Closed').
     * Artinya proses rekrutmen untuk posisi ini telah dihentikan secara manual.
     *
     * @param int $id ID Recruitment Request.
     * @param RrService $service Layanan bisnis RR.
     * @return void
     */
    public function close($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->close($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
    }

    /**
     * Menonaktifkan RR publik (mengubah status dari 'Published' kembali menjadi 'Ready to Publish').
     * Lowongan (Vacancy) terkait akan disembunyikan dari publik.
     *
     * @param int $id ID Recruitment Request.
     * @param RrService $service Layanan bisnis RR.
     * @return void
     */
    public function unpublish($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->unpublish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dinonaktifkan.');
    }

    /**
     * Menghapus data RR secara permanen.
     * Operasi ini hanya bisa dilakukan pada RR berstatus draf yang belum memiliki pelamar.
     *
     * @param int $id ID Recruitment Request.
     * @param RrService $service Layanan bisnis RR.
     * @return void
     */
    public function delete($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        try {
            $service->delete($rr);
            session()->flash('message', 'Recruitment Request berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Merender komponen daftar RR.
     * Mengambil statistik ringkasan dan daftar RR dari RrRepository berdasarkan filter aktif,
     * kemudian menampilkannya di tampilan (view) dengan sistem paginasi.
     *
     * @param \App\Repositories\RrRepository $repository Repositori RR.
     * @return \Illuminate\View\View
     */
    public function render(\App\Repositories\RrRepository $repository)
    {
        $stats = $repository->getStats();

        $filters = [
            'status' => $this->status,
            'search' => $this->search,
            'sortBy' => $this->sortBy,
        ];

        $rrs = $repository->getPaginatedList($filters, 12);

        return view('livewire.rr.rr-index', [
            'rrs' => $rrs,
            'stats' => $stats
        ]);
    }
}
