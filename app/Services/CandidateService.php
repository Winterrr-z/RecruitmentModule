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
            if ($toStage->is_first_stage) {
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
        $finalStage = Stage::where('is_final_stage', true)->first();

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
            $candidate->notify(new \App\Notifications\CandidateRejectedNotification($candidate->vacancy));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email penolakan untuk kandidat {$candidate->id}: " . $e->getMessage());
        }
    }

    /**
     * Blacklist kandidat berdasarkan model Candidate.
     */
    public function blacklistCandidate(Candidate $candidate, string $reason): void
    {
        $this->blacklistDetails($candidate->name, $candidate->email, $candidate->phone, $reason);
    }

    /**
     * Blacklist kandidat berdasarkan nama/email/telepon secara langsung.
     */
    public function blacklistDetails(string $name, string $email, string $phone, string $reason): void
    {
        $finalStage = Stage::where('is_final_stage', true)->first();
        if (!$finalStage) {
            throw new \Exception("Tahap 'Final' tidak ditemukan.");
        }

        DB::transaction(function () use ($name, $email, $phone, $reason, $finalStage) {
            Blacklist::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'reason' => $reason,
            ]);

            // Update all candidate records matching this email or phone
            $candidates = Candidate::where('email', $email)
                ->orWhere('phone', $phone)
                ->get();

            foreach ($candidates as $c) {
                $isActive = !in_array($c->status, [
                    \App\Enums\CandidateStatus::REJECTED,
                    \App\Enums\CandidateStatus::HIRED,
                    \App\Enums\CandidateStatus::DECLINED,
                    \App\Enums\CandidateStatus::EXPIRED,
                    \App\Enums\CandidateStatus::BLACKLISTED
                ]);

                if ($isActive) {
                    if ($c->current_stage_id != $finalStage->id) {
                        CandidateMovement::create([
                            'candidate_id' => $c->id,
                            'from_stage_id' => $c->current_stage_id,
                            'to_stage_id' => $finalStage->id,
                            'moved_at' => now(),
                        ]);
                    }
                    $c->status = \App\Enums\CandidateStatus::REJECTED;
                    $c->current_stage_id = $finalStage->id;
                    $c->save();
                } else {
                    $c->status = \App\Enums\CandidateStatus::BLACKLISTED;
                    $c->current_stage_id = $finalStage->id;
                    $c->save();
                }
            }
        });
    }

    /**
     * Approve kandidat (Hired).
     */
    public function approveCandidate(Candidate $candidate): void
    {
        $currentStage = $candidate->currentStage;
        $finalStage = Stage::where('is_final_stage', true)->first();

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

    /**
     * Proses pengajuan lamaran pekerjaan.
     * Mengembalikan rute redirect atau null jika berhasil.
     */
    public function applyForJob(\App\Models\Vacancy $vacancy, int $userId, array $data, $cvFile, $portofolioFile = null): ?string
    {
        // Cek blacklist
        $isBlacklisted = DB::table('blacklist')
            ->where('email', $data['email'])
            ->orWhere('phone', $data['phone'])
            ->exists();

        if ($isBlacklisted) {
            return route('blacklist.info');
        }

        // Upload file ke storage/app/private/candidates
        $cvPath = $cvFile->store('candidates', 'local');
        $portofolioPath = $portofolioFile 
            ? $portofolioFile->store('candidates', 'local')
            : null;

        // Simpan kandidat
        Candidate::create([
            'vacancy_id' => $vacancy->id,
            'user_id' => $userId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'cv_path' => $cvPath,
            'portofolio_path' => $portofolioPath,
            'current_stage_id' => 1, // Applied
            'status' => \App\Enums\CandidateStatus::APPLIED,
            'source' => 'public',
        ]);

        return null;
    }
}
