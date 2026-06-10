<?php

namespace App\Repositories;

use App\Models\Rr;
use Illuminate\Pagination\LengthAwarePaginator;

class RrRepository
{
    /**
     * Get a paginated list of Recruitment Requests with filters.
     */
    public function getPaginatedList(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Rr::with('vacancy', 'mpp')->withCount('candidates');

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Completed') {
                $query->whereIn('status', ['Completed', 'Closed']);
            } elseif ($filters['status'] === 'Closed') {
                $query->where('status', 'Closed');
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

        $query->orderByRaw("CASE 
            WHEN lower(status) IN ('draft', 'ready to publish') THEN 0 
            WHEN lower(status) = 'published' THEN 1 
            ELSE 2 END ASC");

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
            'total_active' => Rr::where('status', 'Published')->count(),
            'ready_to_publish' => Rr::whereIn('status', ['Draft', 'Ready to Publish'])->count(),
            'completed' => Rr::whereIn('status', ['Completed', 'Closed'])->count(),
        ];
    }
}
