<?php

namespace App\Repositories;

use App\Models\Candidate;

class CandidateRepository
{
    /**
     * Dapatkan semua kandidat dengan filter untuk halaman All Candidates.
     */
    public function getAllCandidates($filterVacancy, $filterStatus, $filterStage, $search, $perPage = 15)
    {
        $latestIds = Candidate::selectRaw('MAX(id)')
            ->groupBy('email');

        $paginator = Candidate::query()
            ->with(['vacancy', 'currentStage'])
            ->whereIn('id', $latestIds)
            ->when($filterVacancy, fn($q) => $q->where('vacancy_id', $filterVacancy))
            ->when($filterStatus, fn($q) => $q->where('status', $filterStatus))
            ->when($filterStage, fn($q) => $q->where('current_stage_id', $filterStage))
            ->when($search, fn($q) => $q->where(function($sub) use ($search) {
                $sub->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            }))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Hitung jumlah lamaran untuk email-email yang ada di halaman ini
        $emails = $paginator->pluck('email')->unique()->all();
        if (!empty($emails)) {
            $counts = Candidate::selectRaw('email, count(*) as count')
                ->whereIn('email', $emails)
                ->groupBy('email')
                ->pluck('count', 'email')
                ->toArray();

            $paginator->through(function($candidate) use ($counts) {
                $candidate->applications_count = $counts[$candidate->email] ?? 1;
                return $candidate;
            });
        }

        return $paginator;
    }

    /**
     * Dapatkan kandidat untuk pipeline view.
     */
    public function getPipelineCandidates($selectedVacancyId, $selectedStageId, $search, $perPage = 10)
    {
        return Candidate::with('vacancy', 'currentStage')
            ->when($selectedVacancyId, fn($q) => $q->where('vacancy_id', $selectedVacancyId))
            ->when($selectedStageId, fn($q) => $q->where('current_stage_id', $selectedStageId))
            ->when($search, fn($q) => $q->where(fn($sq) => $sq->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')))
            ->where('status', '!=', \App\Enums\CandidateStatus::BLACKLISTED)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Hitung jumlah kandidat per stage.
     */
    public function getStageCounts($selectedVacancyId, $search)
    {
        return Candidate::when($selectedVacancyId, fn($q) => $q->where('vacancy_id', $selectedVacancyId))
            ->when($search, fn($q) => $q->where(fn($sq) => $sq->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%')))
            ->where('status', '!=', \App\Enums\CandidateStatus::BLACKLISTED)
            ->selectRaw('current_stage_id, count(*) as count')
            ->groupBy('current_stage_id')
            ->pluck('count', 'current_stage_id')
            ->toArray();
    }
}
