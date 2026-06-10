<?php

namespace App\Livewire\Rr;

use App\Models\Rr;
use App\Models\Candidate;
use App\Models\Stage;
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
     * @return void
     */
    public function publish()
    {
        $rr = Rr::findOrFail($this->rrId);
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

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil dipublikasikan.');
        }
    }

    /**
     * Nonaktifkan RR (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @return void
     */
    public function unpublish()
    {
        $rr = Rr::findOrFail($this->rrId);
        if ($rr->status->value === 'Published') {
            $rr->update(['status' => 'Ready to Publish']);

            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => 'Draft']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" dinonaktifkan.');
        }
    }

    /**
     * Tutup RR (ubah status ke 'Closed').
     */
    public function close()
    {
        $rr = Rr::findOrFail($this->rrId);

        if ($rr->status->value !== 'Closed' && $rr->status->value !== 'Completed') {
            $rr->update(['status' => 'Closed']);

            // Tutup vacancy juga
            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => 'Closed']);
            }

            session()->flash('message', 'Recruitment Request "' . $rr->job_title . '" berhasil ditutup.');
        }
    }

    /**
     * Hapus RR draft.
     *
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function delete()
    {
        $rr = Rr::with('vacancy.candidates')->findOrFail($this->rrId);

        if ($rr->hiredCount() > 0 || ($rr->status->value !== 'Draft' && $rr->status->value !== 'Ready to Publish')) {
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
        $rr = Rr::with('mpp', 'vacancy')->findOrFail($this->rrId);

        $vacancyId = $rr->vacancy?->id;

        // Ambil metrik kandidat
        $totalCandidates = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)->count() : 0;
        $hiredCandidates = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)->where('status', \App\Enums\CandidateStatus::HIRED)->count() : 0;
        $rejectedCandidates = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)->where('status', \App\Enums\CandidateStatus::REJECTED)->count() : 0;
        $activeCandidates = $vacancyId ? Candidate::where('vacancy_id', $vacancyId)->whereNotIn('status', [\App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::DECLINED, \App\Enums\CandidateStatus::EXPIRED])->count() : 0;

        // Ambil persebaran kandidat per stage
        $stages = Stage::getAllCached()->map(function ($stage) use ($vacancyId) {
            return [
                'name' => $stage->name,
                'count' => $vacancyId ? Candidate::where('vacancy_id', $vacancyId)
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
        ]);
    }
}
