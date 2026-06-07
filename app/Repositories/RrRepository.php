<?php

namespace App\Repositories;

use App\Models\RecruitmentRequest;
use Illuminate\Pagination\LengthAwarePaginator;

class RrRepository
{
    /**
     * Get a paginated list of Recruitment Requests with filters.
     */
    public function getPaginatedList(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = RecruitmentRequest::with('lowongan', 'mpp')->withCount('candidates');

        if (!empty($filters['status'])) {
            if (in_array($filters['status'], ['Completed/Closed', 'Completed', 'Closed'])) {
                $query->whereIn('status', ['Completed/Closed', 'Completed', 'Closed']);
            } else {
                $query->whereRaw('lower(status) = ?', [strtolower($filters['status'])]);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('job_title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('department', 'like', '%' . $filters['search'] . '%');
            });
        }

        $query->orderByRaw("CASE WHEN lower(status) IN ('completed/closed', 'completed', 'closed') THEN 1 ELSE 0 END ASC");

        if (($filters['sortBy'] ?? 'newest') === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get RR dashboard statistics.
     */
    public function getStats(): array
    {
        return [
            'total_active' => RecruitmentRequest::where('status', 'Published')->count(),
            'ready_to_publish' => RecruitmentRequest::whereIn('status', ['Draft', 'Ready to Publish'])->count(),
            'completed' => RecruitmentRequest::whereIn('status', ['Completed/Closed', 'Completed', 'Closed'])->count(),
        ];
    }
}
