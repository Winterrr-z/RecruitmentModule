<?php

namespace App\Livewire\Rr;

use App\Models\RecruitmentRequest;
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
#[Layout('layouts.app')]
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
     * Akan otomatis membuat entri Lowongan untuk publik.
     *
     * @param int $id
     * @return void
     */
    public function publish($id)
    {
        $rr = RecruitmentRequest::findOrFail($id);
        if ($rr->status === 'Draft' || $rr->status === 'Ready to Publish') {
            $rr->update(['status' => 'Published']);

            // Buat Lowongan otomatis
            $rr->lowongan()->updateOrCreate(
                ['recruitment_request_id' => $rr->id],
                [
                    'kuota' => $rr->kuota,
                    'jabatan' => $rr->jabatan,
                    'departemen' => $rr->departemen,
                    'tipe_kerja' => $rr->tipe_kerja,
                    'lokasi' => $rr->lokasi,
                    'application_deadline' => $rr->application_deadline,
                    'tampilkan_gaji' => $rr->tampilkan_gaji,
                    'estimasi_gaji_min' => $rr->estimasi_gaji_min,
                    'estimasi_gaji_max' => $rr->estimasi_gaji_max,
                    'deskripsi_pekerjaan' => $rr->deskripsi_pekerjaan,
                    'spesifikasi_kebutuhan' => $rr->spesifikasi_kebutuhan,
                    'status' => 'Published'
                ]
            );

            session()->flash('message', 'Recruitment Request "' . $rr->jabatan . '" berhasil dipublikasikan dan Lowongan telah dibuat.');
        }
    }

    /**
     * Tutup RR (ubah status ke 'Completed/Closed').
     * Akan otomatis menutup Lowongan publik.
     *
     * @param int $id
     * @return void
     */
    public function close($id)
    {
        $rr = RecruitmentRequest::findOrFail($id);
        if ($rr->status !== 'Completed/Closed' && $rr->status !== 'Closed' && $rr->status !== 'Completed') {
            $rr->update(['status' => 'Completed/Closed']);

            // Tutup juga lowongan jika ada
            if ($rr->lowongan) {
                $rr->lowongan->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->jabatan . '" berhasil ditutup.');
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
        $rr = RecruitmentRequest::findOrFail($id);
        if ($rr->status === 'Published') {
            $rr->update(['status' => 'Ready to Publish']);

            // Nonaktifkan lowongan terkait
            if ($rr->lowongan) {
                $rr->lowongan->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->jabatan . '" berhasil dinonaktifkan.');
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
        $rr = RecruitmentRequest::with('lowongan.candidates')->findOrFail($id);

        if ($rr->hiredCount() > 0 || $rr->status !== 'Ready to Publish') {
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
    public function render()
    {
        $stats = [
            'total_active' => RecruitmentRequest::where('status', 'Published')->count(),
            'ready_to_publish' => RecruitmentRequest::whereIn('status', ['Draft', 'Ready to Publish'])->count(),
            'completed' => RecruitmentRequest::whereIn('status', ['Completed/Closed', 'Completed', 'Closed'])->count(),
        ];

        // Query RR
        $query = RecruitmentRequest::with('lowongan', 'mpp')->withCount('candidates');

        if ($this->status !== '') {
            if (in_array($this->status, ['Completed/Closed', 'Completed', 'Closed'])) {
                $query->whereIn('status', ['Completed/Closed', 'Completed', 'Closed']);
            } else {
                $query->whereRaw('lower(status) = ?', [strtolower($this->status)]);
            }
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('jabatan', 'like', '%' . $this->search . '%')
                  ->orWhere('departemen', 'like', '%' . $this->search . '%');
            });
        }

        $query->orderByRaw("CASE WHEN lower(status) IN ('completed/closed', 'completed', 'closed') THEN 1 ELSE 0 END ASC");

        if ($this->sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rrs = $query->paginate(12);

        return view('livewire.rr.rr-index', [
            'rrs' => $rrs,
            'stats' => $stats
        ]);
    }
}
