<?php

namespace App\Repositories;

use App\Models\Mpp;
use App\Enums\CandidateStatus;
use Illuminate\Pagination\LengthAwarePaginator;

class MppRepository
{
    /**
     * Get a paginated list of Manpower Plans with filters and hired count logic.
     */
    public function getPaginatedList(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Mpp::with('vacancies.candidates')
            ->select('mpps.*')
            ->selectSub(function ($q) {
                $q->selectRaw('count(*)')
                  ->from('candidates')
                  ->join('vacancies', 'vacancies.id', '=', 'candidates.vacancy_id')
                  ->join('rrs', 'rrs.id', '=', 'vacancies.rr_id')
                  ->whereColumn('rrs.mpp_id', 'mpps.id')
                  ->where('candidates.status', CandidateStatus::HIRED);
            }, 'hired_count');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('plan_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('job_title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('department', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->havingRaw('hired_count >= quota');
            } else {
                $query->where('status', $filters['status']);
            }
        }

        $query->orderByRaw("CASE WHEN lower(status) = 'closed' OR hired_count >= quota THEN 1 ELSE 0 END ASC");

        if (($filters['sortBy'] ?? 'newest') === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all unique departments available in MPPs.
     */
    public function getUniqueDepartments()
    {
        return Mpp::select('department')
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');
    }
}
