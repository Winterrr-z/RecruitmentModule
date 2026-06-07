<?php

namespace App\Services;

use App\Models\Mpp;
use App\Enums\MppStatus;

class MppService
{
    /**
     * Approve a Manpower Plan.
     */
    public function approve(Mpp $mpp): bool
    {
        if ($mpp->status === MppStatus::DRAFT) {
            $mpp->update([
                'status' => MppStatus::APPROVED,
                'last_activity_at' => now()
            ]);
            return true;
        }
        return false;
    }

    /**
     * Close a Manpower Plan.
     */
    public function close(Mpp $mpp): bool
    {
        if ($mpp->status === MppStatus::APPROVED && !$mpp->hasActiveCandidates()) {
            $mpp->update([
                'status' => MppStatus::CLOSED,
                'last_activity_at' => now()
            ]);
            return true;
        }
        return false;
    }
}
