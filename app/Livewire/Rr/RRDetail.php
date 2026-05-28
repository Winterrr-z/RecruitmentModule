<?php

namespace App\Livewire\Rr;

use App\Models\RecruitmentRequest;
use App\Models\Candidate;
use App\Models\Stage;
use Livewire\Component;

/**
 * Class RRDetail
 * 
 * Komponen Livewire untuk menampilkan detail spesifik dari Recruitment Request (RR).
 * Menampilkan detail posisi, pengaturan publikasi, deskripsi pekerjaan, spesifikasi kebutuhan,
 * informasi MPP terhubung, serta statistik pelamar per stage.
 *
 * @package App\Livewire
 */
class RRDetail extends Component
{
    /**
     * @var int ID dari RR yang sedang dilihat.
     */
    public $rrId;

    /**
     * Inisialisasi komponen dengan rrId.
     *
     * @param int $id
     * @return void
     */
    public function mount($id)
    {
        $this->rrId = $id;
    }

    /**
     * Publikasikan RR (ubah status dari 'Draft' ke 'Published').
     *
     * @return void
     */
    public function publish()
    {
        $rr = RecruitmentRequest::findOrFail($this->rrId);
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

            session()->flash('message', 'Recruitment Request "' . $rr->jabatan . '" berhasil dipublikasikan.');
        }
    }

    /**
     * Tutup RR (ubah status ke 'Completed/Closed').
     *
     * @return void
     */
    public function close()
    {
        $rr = RecruitmentRequest::findOrFail($this->rrId);
        if ($rr->status !== 'Completed/Closed') {
            $rr->update(['status' => 'Completed/Closed']);

            // Tutup lowongan juga
            if ($rr->lowongan) {
                $rr->lowongan->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->jabatan . '" berhasil ditutup.');
        }
    }

    /**
     * Hapus RR draft.
     *
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function delete()
    {
        $rr = RecruitmentRequest::with('lowongan.candidates')->findOrFail($this->rrId);

        if ($rr->hiredCount() > 0 || ($rr->status !== 'Draft' && $rr->status !== 'Ready to Publish')) {
            session()->flash('error', 'Recruitment Request yang memiliki pelamar Hired atau statusnya bukan Draft tidak dapat dihapus.');
            return;
        }

        $rr->delete();
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
        $rr = RecruitmentRequest::with('mpp', 'lowongan')->findOrFail($this->rrId);

        $lowonganId = $rr->lowongan?->id;

        // Ambil metrik kandidat
        $totalCandidates = $lowonganId ? Candidate::where('lowongan_id', $lowonganId)->count() : 0;
        $hiredCandidates = $lowonganId ? Candidate::where('lowongan_id', $lowonganId)->where('status', 'Hired')->count() : 0;
        $rejectedCandidates = $lowonganId ? Candidate::where('lowongan_id', $lowonganId)->where('status', 'Rejected')->count() : 0;
        $activeCandidates = $lowonganId ? Candidate::where('lowongan_id', $lowonganId)->whereNotIn('status', ['Hired', 'Rejected', 'Declined', 'Expired'])->count() : 0;

        // Ambil persebaran kandidat per stage
        $stages = Stage::orderBy('urutan')->get()->map(function ($stage) use ($lowonganId) {
            return [
                'nama' => $stage->nama,
                'count' => $lowonganId ? Candidate::where('lowongan_id', $lowonganId)
                    ->where('current_stage_id', $stage->id)
                    ->count() : 0
            ];
        });

        return view('livewire.rr.rr-detail', [
            'rr' => $rr,
            'totalCandidates' => $totalCandidates,
            'hiredCandidates' => $hiredCandidates,
            'rejectedCandidates' => $rejectedCandidates,
            'activeCandidates' => $activeCandidates,
            'stages' => $stages,
        ])->layout('layouts.app');
    }
}
