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
 * Komponen Livewire untuk menampilkan detail spesifik dari Recruitment Request (RR).
 * Menampilkan detail posisi, pengaturan publikasi, deskripsi pekerjaan, spesifikasi kebutuhan,
 * informasi MPP terhubung, serta statistik pelamar per stage.
 *
 * @package App\Livewire
 */
#[Layout('layouts.hr')]
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
     * @param RrService $service
     * @return void
     */
    public function publish(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->publish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan.');
    }

    /**
     * Nonaktifkan RR (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @param RrService $service
     * @return void
     */
    public function unpublish(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->unpublish($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" dinonaktifkan.');
    }

    /**
     * Tutup RR (ubah status ke 'Closed').
     *
     * @param RrService $service
     * @return void
     */
    public function close(RrService $service)
    {
        $rr = Rr::findOrFail($this->rrId);
        $service->close($rr);
        session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
    }

    /**
     * Hapus RR draft.
     *
     * @param RrService $service
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
     * Render komponen Livewire.
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
                \App\Enums\CandidateStatus::DECLINED->value,
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
