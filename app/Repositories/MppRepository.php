<?php

namespace App\Repositories;

use App\Models\Mpp;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MppRepository
{
    /**
     * Get unique list of departments for filtering.
     */
    public function getUniqueDepartments(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('mpp_unique_departments', 86400, function () {
            return Mpp::select('department')
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department');
        });
    }

    /**
     * Get paginated list of MPP with eager loaded relationships and computed fields via subqueries.
     */
    public function getPaginatedList(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Mpp::with('rrs.vacancy.candidates')
            ->select('mpps.*')
            // Subquery for hired count
            ->selectSub(function ($q) {
                $q->selectRaw('count(*)')
                  ->from('candidates')
                  ->join('vacancies', 'vacancies.id', '=', 'candidates.vacancy_id')
                  ->join('rrs', 'rrs.id', '=', 'vacancies.rr_id')
                  ->whereColumn('rrs.mpp_id', 'mpps.id')
                  ->where('candidates.status', \App\Enums\CandidateStatus::HIRED->value);
            }, 'hired_count')
            // Subquery for latest RR created_at
            ->selectSub(function ($q) {
                $q->selectRaw('max(created_at)')
                  ->from('rrs')
                  ->whereColumn('mpp_id', 'mpps.id');
            }, 'latest_rr_date')
            // Subquery for latest candidate created_at
            ->selectSub(function ($q) {
                $q->selectRaw('max(candidates.created_at)')
                  ->from('candidates')
                  ->join('vacancies', 'vacancies.id', '=', 'candidates.vacancy_id')
                  ->join('rrs', 'rrs.id', '=', 'vacancies.rr_id')
                  ->whereColumn('rrs.mpp_id', 'mpps.id');
            }, 'latest_candidate_date')
            // Subquery for latest candidate movement moved_at
            ->selectSub(function ($q) {
                $q->selectRaw('max(candidate_movements.moved_at)')
                  ->from('candidate_movements')
                  ->join('candidates', 'candidates.id', '=', 'candidate_movements.candidate_id')
                  ->join('vacancies', 'vacancies.id', '=', 'candidates.vacancy_id')
                  ->join('rrs', 'rrs.id', '=', 'vacancies.rr_id')
                  ->whereColumn('rrs.mpp_id', 'mpps.id');
            }, 'latest_movement_date');

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', '%' . $search . '%')
                  ->orWhere('job_title', 'like', '%' . $search . '%')
                  ->orWhere('department', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply sorting
        $sortBy = $filters['sortBy'] ?? 'status_priority';
        if ($sortBy === 'newest') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sortBy === 'status_priority') {
            $query->orderByRaw("CASE 
                WHEN status = 'Draft' THEN 1 
                WHEN status = 'Approved' THEN 2 
                WHEN status = 'Completed' THEN 3 
                WHEN status = 'Closed' THEN 4 
                ELSE 5 
            END ASC")
            ->orderBy('created_at', 'desc');
        } else {
            // Default ordering
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }
}
