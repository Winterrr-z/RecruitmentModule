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
 * Komponen Livewire untuk menampilkan daftar Recruitment Request (RR).
 * Menangani pencarian, filter status, pagination, serta aksi publish dan close RR.
 *
 * @package App\Livewire
 */
#[Layout('layouts.hr')]
class RRIndex extends Component
{
    use WithPagination;

    /**
     * @var string Query pencarian jabatan atau departemen.
     */
    #[Url]
    public $search = '';

    /**
     * @var string Filter status RR.
     */
    #[Url]
    public $status = '';

    /**
     * @var string Filter sortBy.
     */
    #[Url]
    public $sortBy = 'newest';

    /**
     * Reset pagination page ketika pencarian atau filter diubah.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'status', 'sortBy'])) {
            $this->resetPage();
        }
    }

    /**
     * Publish RR (ubah status dari 'Draft' ke 'Published').
     * Akan otomatis membuat entri Vacancy untuk publik.
     *
     * @param int $id
     * @param RrService $service
     * @return void
     */
    public function publish($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->publish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan dan Vacancy telah dibuat.');
    }

    /**
     * Tutup RR (ubah status ke 'Closed').
     *
     * @param int $id
     * @param RrService $service
     * @return void
     */
    public function close($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->close($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
    }

    /**
     * Nonaktifkan RR (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @param int $id
     * @param RrService $service
     * @return void
     */
    public function unpublish($id, RrService $service)
    {
        $rr = Rr::findOrFail($id);
        $service->unpublish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dinonaktifkan.');
    }

    /**
     * Hapus RR draft jika tidak memiliki pelamar.
     *
     * @param int $id
     * @param RrService $service
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
     * Render komponen Livewire.
     *
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
