<?php

namespace App\Livewire\Rr;

use App\Models\Rr;
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
     * @return void
     */
    public function publish($id)
    {
        $rr = Rr::findOrFail($id);
        if ($rr->status->value === 'Draft' || $rr->status->value === 'Ready to Publish') {
            $rr->update(['status' => 'Published']);

            // Buat Vacancy otomatis
            $rr->vacancy()->updateOrCreate(
                ['rr_id' => $rr->id],
                [
                    'quota' => $rr->quota,
                    'job_title' => $rr->job_title,
                    'department' => $rr->department,
                    'employment_type' => $rr->employment_type,
                    'location' => $rr->location,
                    'application_deadline' => $rr->application_deadline,
                    'show_salary' => $rr->show_salary,
                    'estimated_salary_min' => $rr->estimated_salary_min,
                    'estimated_salary_max' => $rr->estimated_salary_max,
                    'job_description' => $rr->job_description,
                    'job_requirements' => $rr->job_requirements,
                    'status' => 'Published'
                ]
            );

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan dan Vacancy telah dibuat.');
        }
    }

    /**
     * Tutup RR (ubah status ke 'Closed').
     *
     * @param int $id
     * @return void
     */
    public function close($id)
    {
        $rr = Rr::findOrFail($id);

        if ($rr->status->value !== 'Closed' && $rr->status->value !== 'Completed') {
            $rr->update(['status' => 'Closed']);

            // Tutup juga vacancy jika ada
            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
        }
     }

    /**
     * Nonaktifkan RR (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @param int $id
     * @return void
     */
    public function unpublish($id)
    {
        $rr = Rr::findOrFail($id);
        if ($rr->status->value === 'Published') {
            $rr->update(['status' => 'Ready to Publish']);

            // Nonaktifkan vacancy terkait
            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dinonaktifkan.');
        }
    }

    /**
     * Hapus RR draft jika tidak memiliki pelamar.
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $rr = Rr::with('vacancy.candidates')->findOrFail($id);

        if ($rr->hiredCount() > 0 || $rr->status->value !== 'Ready to Publish') {
            session()->flash('error', 'Recruitment Request yang memiliki pelamar Hired atau statusnya bukan Ready to Publish tidak dapat dihapus.');
            return;
        }

        $rr->delete();
        session()->flash('message', 'Recruitment Request berhasil dihapus.');
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
