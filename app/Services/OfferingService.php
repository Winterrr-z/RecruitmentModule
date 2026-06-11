<?php

namespace App\Services;

use App\Models\Candidate;
use App\Enums\CandidateStatus;
use App\Enums\VacancyStatus;
use App\Enums\RrStatus;
use App\Models\Vacancy;
use App\Notifications\CandidateRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferingService
{
    /**
     * Accept job offering.
     */
    public function acceptOffering(Candidate $candidate): void
    {
        DB::transaction(function () use ($candidate) {
            $candidate->status = CandidateStatus::HIRED;
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();

            // Use pessimistic locking to prevent race condition when reducing quota
            $vacancy = $candidate->vacancy()->lockForUpdate()->first();
            if ($vacancy) {
                $vacancy->quota = max(0, $vacancy->quota - 1);

                if ($vacancy->quota == 0) {
                    $vacancy->status = VacancyStatus::CLOSED;
                    $vacancy->save();

                    $rr = $vacancy->rr;
                    if ($rr) {
                        $rr->status = RrStatus::COMPLETED;
                        $rr->save();

                        $mpp = $rr->mpp;
                        if ($mpp && $mpp->isFilled()) {
                            $mpp->status = \App\Enums\MppStatus::COMPLETED;
                            $mpp->save();
                        }
                    }

                    // Auto-Reject other candidates
                    $rejectedCandidates = Candidate::where('vacancy_id', $vacancy->id)
                        ->whereIn('status', [CandidateStatus::APPLIED, CandidateStatus::IN_PROGRESS, CandidateStatus::OFFERED])
                        ->where('id', '!=', $candidate->id)
                        ->get();

                    foreach ($rejectedCandidates as $rejected) {
                        $rejected->status = CandidateStatus::REJECTED;
                        $rejected->save();

                        try {
                            $rejected->notify(new CandidateRejectedNotification($vacancy));
                        } catch (\Exception $e) {
                            Log::error("Gagal mengirim email penolakan otomatis untuk kandidat {$rejected->id}: " . $e->getMessage());
                        }
                    }
                } else {
                    $vacancy->save();
                }
            }
        });
    }

    /**
     * Decline job offering.
     */
    public function declineOffering(Candidate $candidate): void
    {
        DB::transaction(function () use ($candidate) {
            $candidate->status = CandidateStatus::DECLINED;
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();
        });
    }
}
