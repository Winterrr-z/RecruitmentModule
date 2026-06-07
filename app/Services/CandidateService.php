<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\CandidateMovement;
use App\Models\Blacklist;
use App\Models\Scorecard;
use App\Models\InterviewSchedule;
use Illuminate\Support\Facades\DB;

class CandidateService
{
    /**
     * Memeriksa apakah kandidat memenuhi syarat untuk pindah dari stage saat ini.
     */
    public function validateCurrentStageRequirements(Candidate $candidate): ?string
    {
        $currentStage = $candidate->currentStage;
        if (!$currentStage) {
            return null;
        }

        if ($currentStage->needs_scorecard) {
            $scorecardCount = Scorecard::where('candidate_id', $candidate->id)
                ->where('stage_id', $currentStage->id)
                ->count();
            if ($scorecardCount === 0) {
                return "Kandidat '{$candidate->name}' tidak dapat dipindahkan karena tahap saat ini ('{$currentStage->name}') membutuhkan scorecard yang belum diisi.";
            }
        }

        if ($currentStage->needs_schedule) {
            $scheduleCount = InterviewSchedule::where('candidate_id', $candidate->id)
                ->where('stage_id', $currentStage->id)
                ->count();
            if ($scheduleCount === 0) {
                return "Kandidat '{$candidate->name}' tidak dapat dipindahkan karena tahap saat ini ('{$currentStage->name}') membutuhkan jadwal interview yang belum dibuat.";
            }
        }

        return null;
    }

    /**
     * Pindahkan kandidat ke stage baru.
     */
    public function moveCandidate(Candidate $candidate, Stage $toStage): void
    {
        if ($candidate->current_stage_id == $toStage->id) {
            return;
        }

        DB::transaction(function () use ($candidate, $toStage) {
            CandidateMovement::create([
                'candidate_id' => $candidate->id,
                'from_stage_id' => $candidate->current_stage_id,
                'to_stage_id' => $toStage->id,
                'moved_at' => now(),
            ]);

            $newStatus = \App\Enums\CandidateStatus::IN_PROGRESS;
            if ($toStage->id == 1 || strtolower($toStage->name) === 'applied') {
                $newStatus = \App\Enums\CandidateStatus::APPLIED;
            }

            $candidate->update([
                'current_stage_id' => $toStage->id,
                'status' => $newStatus,
            ]);
        });
    }

    /**
     * Tolak kandidat.
     */
    public function rejectCandidate(Candidate $candidate): void
    {
        $currentStage = $candidate->currentStage;
        $finalStage = Stage::where('name', 'Final')->orWhere('id', 2)->first();

        if (!$finalStage) {
            throw new \Exception("Tahap 'Final' tidak ditemukan.");
        }

        DB::transaction(function () use ($candidate, $currentStage, $finalStage) {
            if ($candidate->current_stage_id != $finalStage->id) {
                CandidateMovement::create([
                    'candidate_id' => $candidate->id,
                    'from_stage_id' => $currentStage->id,
                    'to_stage_id' => $finalStage->id,
                    'moved_at' => now(),
                ]);
                $candidate->current_stage_id = $finalStage->id;
            }
            $candidate->status = \App\Enums\CandidateStatus::REJECTED;
            $candidate->save();
        });

        try {
            $candidate->notify(new \App\Notifications\CandidateRejectedNotification($candidate->lowongan));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email penolakan untuk kandidat {$candidate->id}: " . $e->getMessage());
        }
    }

    /**
     * Blacklist kandidat.
     */
    public function blacklistCandidate(Candidate $candidate, string $reason): void
    {
        $finalStage = Stage::where('name', 'Final')->orWhere('id', 2)->first();
        if (!$finalStage) {
            throw new \Exception("Tahap 'Final' tidak ditemukan.");
        }

        DB::transaction(function () use ($candidate, $finalStage, $reason) {
            Blacklist::create([
                'name' => $candidate->name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'reason' => $reason,
            ]);

            if ($candidate->current_stage_id != $finalStage->id) {
                CandidateMovement::create([
                    'candidate_id' => $candidate->id,
                    'from_stage_id' => $candidate->current_stage_id,
                    'to_stage_id' => $finalStage->id,
                    'moved_at' => now(),
                ]);
                $candidate->current_stage_id = $finalStage->id;
            }

            $candidate->status = \App\Enums\CandidateStatus::BLACKLISTED;
            $candidate->save();
        });
    }

    /**
     * Approve kandidat (Hired).
     */
    public function approveCandidate(Candidate $candidate): void
    {
        $currentStage = $candidate->currentStage;
        $finalStage = Stage::where('name', 'Final')->orWhere('id', 2)->first();

        if (!$finalStage) {
            throw new \Exception("Tahap 'Final' tidak ditemukan.");
        }

        DB::transaction(function () use ($candidate, $currentStage, $finalStage) {
            if ($candidate->current_stage_id != $finalStage->id) {
                CandidateMovement::create([
                    'candidate_id' => $candidate->id,
                    'from_stage_id' => $currentStage->id,
                    'to_stage_id' => $finalStage->id,
                    'moved_at' => now(),
                ]);
                $candidate->current_stage_id = $finalStage->id;
            }

            $candidate->status = \App\Enums\CandidateStatus::OFFERED;
            $candidate->save();
        });
    }
}
