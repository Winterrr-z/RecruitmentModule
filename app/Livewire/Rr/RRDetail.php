<?php

namespace App\Livewire\Rr;

use App\Models\Lowongan;
use App\Models\Candidate;
use App\Models\Stage;
use Livewire\Component;

/**
 * Class RRDetail
 * 
 * Komponen Livewire untuk menampilkan detail spesifik dari Recruitment Request (RR) / Lowongan.
 * Menampilkan detail posisi, pengaturan publikasi, deskripsi pekerjaan, spesifikasi kebutuhan,
 * informasi MPP terhubung, serta statistik pelamar per stage.
 *
 * @package App\Livewire
 */
class RRDetail extends Component
{
    /**
     * @var int ID dari lowongan (RR) yang sedang dilihat.
     */
    public $rrId;

    /**
     * Inisialisasi komponen dengan mppId/lowonganId.
     *
     * @param int $id
     * @return void
     */
    public function mount($id)
    {
        $this->rrId = $id;
    }

    /**
     * Publikasikan lowongan (ubah status dari 'Ready to Publish' ke 'Published').
     *
     * @return void
     */
    public function publish()
    {
        $lowongan = Lowongan::findOrFail($this->rrId);
        if ($lowongan->status === 'Ready to Publish') {
            $lowongan->update(['status' => 'Published']);
            session()->flash('message', 'Lowongan "' . $lowongan->jabatan . '" berhasil diaktifkan.');
        }
    }

    /**
     * Nonaktifkan lowongan (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @return void
     */
    public function unpublish()
    {
        $lowongan = Lowongan::findOrFail($this->rrId);
        if ($lowongan->status === 'Published') {
            $lowongan->update(['status' => 'Ready to Publish']);
            session()->flash('message', 'Lowongan "' . $lowongan->jabatan . '" berhasil dinonaktifkan.');
        }
    }

    /**
     * Tutup lowongan (ubah status dari 'Published' ke 'Completed/Closed').
     *
     * @return void
     */
    public function close()
    {
        $lowongan = Lowongan::findOrFail($this->rrId);
        if ($lowongan->status === 'Published') {
            $lowongan->update(['status' => 'Completed/Closed']);
            session()->flash('message', 'Lowongan "' . $lowongan->jabatan . '" berhasil ditutup.');
        }
    }

    /**
     * Hapus lowongan draft (Ready to Publish) jika tidak memiliki pelamar.
     *
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function delete()
    {
        $lowongan = Lowongan::with('candidates')->findOrFail($this->rrId);

        // Logika rr tidak dapat didelete ketika terdapat pelamar pada lowongan dan status tidak sama dengan draft.
        if ($lowongan->candidates->count() > 0 || $lowongan->status !== 'Ready to Publish') {
            session()->flash('error', 'Lowongan yang memiliki pelamar atau statusnya bukan Draft tidak dapat dihapus.');
            return;
        }

        $lowongan->delete();
        session()->flash('message', 'Recruitment Request berhasil dihapus.');

        return redirect()->route('rr.index');
    }

    /**
     * Render komponen Livewire.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $lowongan = Lowongan::with('mpp')->findOrFail($this->rrId);

        // Ambil metrik kandidat
        $totalCandidates = Candidate::where('lowongan_id', $this->rrId)->count();
        $hiredCandidates = Candidate::where('lowongan_id', $this->rrId)->where('status', 'Hired')->count();
        $rejectedCandidates = Candidate::where('lowongan_id', $this->rrId)->where('status', 'Ditolak')->count();
        $activeCandidates = Candidate::where('lowongan_id', $this->rrId)->whereNotIn('status', ['Hired', 'Ditolak'])->count();

        // Ambil persebaran kandidat per stage
        $stages = Stage::orderBy('urutan')->get()->map(function ($stage) {
            return [
                'nama' => $stage->nama,
                'count' => Candidate::where('lowongan_id', $this->rrId)
                    ->where('current_stage_id', $stage->id)
                    ->count()
            ];
        });

        return view('livewire.rr.rr-detail', [
            'lowongan' => $lowongan,
            'totalCandidates' => $totalCandidates,
            'hiredCandidates' => $hiredCandidates,
            'rejectedCandidates' => $rejectedCandidates,
            'activeCandidates' => $activeCandidates,
            'stages' => $stages,
        ])->layout('layouts.app');
    }
}
