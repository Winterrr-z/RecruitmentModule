<?php

namespace App\Livewire\Rr;

use App\Models\Rr;
use App\Models\Candidate;
use App\Models\Stage;
use App\Services\RrService;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class RRDetail
 * 
 * Komponen Livewire untuk menampilkan halaman detail dari Permintaan Rekrutmen (Recruitment Request / RR).
 * Menampilkan detail spesifik posisi, status publikasi, deskripsi pekerjaan, 
 * spesifikasi kebutuhan, informasi rencana tenaga kerja (MPP) yang terhubung, 
 * serta menyajikan ringkasan dan statistik kandidat per tahapan (stage).
 *
 * @package App\Livewire\Rr
 */
#[Layout('layouts.hr')]
class RRDetail extends Component
{
    /** @var int ID dari Recruitment Request (RR) yang sedang dilihat. */
    public $rrId;

    /**
     * Inisialisasi komponen pada saat pertama kali dimuat.
     * Menerima parameter ID dari rute (URL).
     *
     * @param int $id ID Recruitment Request.
     * @return void
     */
    public function mount($id)
    {
        $this->rrId = $id;
    }

    /**
     * Memublikasikan RR (mengubah status dari 'Draft' atau 'Ready to Publish' menjadi 'Published').
     * Aksi ini akan otomatis membuat data lowongan (Vacancy) publik.
     *
     * @param RrService $service Layanan bisnis untuk RR.
     * @return void
     */
    public function publish(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->publish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan.');
    }

    /**
     * Menonaktifkan publikasi RR (mengubah status dari 'Published' kembali ke 'Ready to Publish').
     * Aksi ini akan menghentikan pendaftaran lowongan baru di sisi publik.
     *
     * @param RrService $service Layanan bisnis untuk RR.
     * @return void
     */
    public function unpublish(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->unpublish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" dinonaktifkan.');
    }

    /**
     * Menutup RR (mengubah status menjadi 'Closed').
     * Dilakukan ketika rekrutmen dibatalkan atau tidak dilanjutkan.
     *
     * @param RrService $service Layanan bisnis untuk RR.
     * @return void
     */
    public function close(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->close($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
    }

    /**
     * Menghapus data RR (hanya bisa dilakukan jika masih berstatus draft/belum memiliki lamaran).
     * Jika berhasil, pengguna akan dialihkan kembali ke daftar RR.
     *
     * @param RrService $service Layanan bisnis untuk RR.
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function delete(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        try {
            $service->delete($rr);
            session()->flash('message', 'Recruitment Request berhasil dihapus.');
            return redirect()->route('rr.index');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Merender komponen antarmuka halaman detail RR.
     * Memuat data utama RR, kemudian menghitung statistik agregat 
     * kandidat dan sebarannya berdasarkan tahapan (stage).
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $rr = Rr::with('mpp', 'vacancy')->findOrFail($this->rrId);

        $vacancyId = $rr->vacancy?->id;

        // Ambil metrik kandidat secara agregat dalam satu pemanggilan
        $candidatesData = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hired_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status NOT IN (?, ?, ?, ?) THEN 1 ELSE 0 END) as active_count
            ", [
                \App\Enums\CandidateStatus::HIRED->value,
                \App\Enums\CandidateStatus::REJECTED->value,
                \App\Enums\CandidateStatus::HIRED->value,
                \App\Enums\CandidateStatus::REJECTED->value,
                \App\Enums\CandidateStatus::WITHDRAWN->value,
                \App\Enums\CandidateStatus::EXPIRED->value,
            ])
            ->first() : null;

        $totalCandidates = $candidatesData ? (int)$candidatesData->total_count : 0;
        $hiredCandidates = $candidatesData ? (int)$candidatesData->hired_count : 0;
        $rejectedCandidates = $candidatesData ? (int)$candidatesData->rejected_count : 0;
        $activeCandidates = $candidatesData ? (int)$candidatesData->active_count : 0;

        // Ambil persebaran kandidat per stage secara agregat
        $stageCounts = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)
            ->select('current_stage_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('current_stage_id')
            ->get()
            ->pluck('count', 'current_stage_id')
            ->toArray() : [];

        // Petakan ke stage dan hilangkan stage yang tidak memiliki kandidat sama sekali (count = 0)
        $stages = Stage::getAllCached()
            ->map(function ($stage) use ($stageCounts) {
                return [
                    'name' => $stage->name,
                    'count' => $stageCounts[$stage->id] ?? 0
                ];
            })
            ->filter(function ($stageInfo) {
                return $stageInfo['count'] > 0;
            })
            ->values();

        return view('livewire.rr.rr-detail', [
            'rr' => $rr,
            'totalCandidates' => $totalCandidates,
            'hiredCandidates' => $hiredCandidates,
            'rejectedCandidates' => $rejectedCandidates,
            'activeCandidates' => $activeCandidates,
            'stages' => $stages,
        ]);
    }
}
