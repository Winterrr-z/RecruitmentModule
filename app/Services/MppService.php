<?php

namespace App\Services;

use App\Models\Mpp;
use App\Enums\MppStatus;

class MppService
{
    /**
     * Approve the Manpower Planning.
     */
    public function approve(Mpp $mpp): bool
    {
        if ($mpp->status === MppStatus::DRAFT) {
            return $mpp->update([
                'status' => MppStatus::APPROVED,
                'last_activity_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Close the Manpower Planning.
     */
    public function close(Mpp $mpp): bool
    {
        if (in_array($mpp->status, [MppStatus::APPROVED, MppStatus::DRAFT]) && !$mpp->hasActiveCandidates()) {
            return $mpp->update([
                'status' => MppStatus::CLOSED,
                'last_activity_at' => now(),
            ]);
        }
        return false;
    }
}
