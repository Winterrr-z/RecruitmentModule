<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mpp;
use App\Models\Rr;
use App\Models\Vacancy;
use App\Models\Stage;
use App\Models\Candidate;
use App\Models\CandidateMovement;
use App\Models\InterviewSchedule;
use App\Models\Scorecard;
use App\Models\Blacklist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    private int $candidateEmailCounter = 31;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key constraints to allow truncating
        Schema::disableForeignKeyConstraints();

        // Clear existing data
        User::where('role', 'applicant')->delete();
        Mpp::truncate();
        Rr::truncate();
        Vacancy::truncate();
        Stage::whereNotIn('id', [1, 2])->delete();
        Candidate::truncate();
        CandidateMovement::truncate();
        InterviewSchedule::truncate();
        Scorecard::truncate();
        Blacklist::truncate();

        Schema::enableForeignKeyConstraints();

        // 1. Seed Users
        // HR User
        User::updateOrCreate(['email' => 'hr1@company.com'], [
            'name' => 'HR Manager',
            'password' => Hash::make('HrPassword'),
            'role' => 'hr',
        ]);
        User::updateOrCreate(['email' => 'hr2@company.com'], [
            'name' => 'HR Staff',
            'password' => Hash::make('HrPassword'),
            'role' => 'hr',
        ]);
        User::updateOrCreate(['email' => 'hr3@company.com'], [
            'name' => 'HR Staff',
            'password' => Hash::make('HrPassword'),
            'role' => 'hr',
        ]);

        // Applicant Users
        $applicants = collect();
        for ($i = 1; $i <= 30; $i++) {
            $applicants->push(User::create([
                'name' => "Kandidat {$i}",
                'email' => "kandidat{$i}@example.com",
                'password' => Hash::make('AppPassword'),
                'role' => 'applicant',
            ]));
        }

        // 2. Seed Stages
        $stageApplied = Stage::updateOrCreate(['id' => 1], [
            'name' => 'Applied',
            'description' => 'Kandidat baru saja melamar vacancy',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 1,
            'is_first_stage' => true,
        ]);

        $stageScreening = Stage::updateOrCreate(['id' => 3], [
            'name' => 'Screening',
            'description' => 'Penyaringan berkas dan kualifikasi awal',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 2,
        ]);

        $stageInterview = Stage::updateOrCreate(['id' => 4], [
            'name' => 'Interview',
            'description' => 'Wawancara dengan HR atau User',
            'needs_scorecard' => false,
            'needs_schedule' => true,
            'sequence' => 3,
        ]);

        $stageTechnical = Stage::updateOrCreate(['id' => 5], [
            'name' => 'Technical Test',
            'description' => 'Ujian kompetensi teknis',
            'needs_scorecard' => true,
            'needs_schedule' => false,
            'sequence' => 4,
            'scorecard_criteria' => [
                ['criteria' => 'Problem Solving & Logic', 'weight' => 40],
                ['criteria' => 'Technical Knowledge', 'weight' => 40],
                ['criteria' => 'Communication Skill', 'weight' => 20],
            ],
        ]);
        
        $stageFinal = Stage::updateOrCreate(['id' => 2], [
            'name' => 'Final',
            'description' => 'Tahap keputusan penawaran (offering)',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 999,
            'is_final_stage' => true,
        ]);

        $stagesArray = [$stageApplied, $stageScreening, $stageInterview, $stageTechnical, $stageFinal];
        $activeUserIds = []; // Track who is already active

        // 3. Seed MPPs based on real business scenarios

        // Scenario 1: Draft MPP (No RR)
        for ($i = 1; $i <= 3; $i++) {
            Mpp::factory()->create([
                'plan_name' => "Draft Plan {$i}",
                'status' => \App\Enums\MppStatus::DRAFT,
            ]);
        }

        // Scenario 2: Approved MPP (But no RR yet)
        for ($i = 1; $i <= 2; $i++) {
            Mpp::factory()->create([
                'plan_name' => "Approved Empty Plan {$i}",
                'status' => \App\Enums\MppStatus::APPROVED,
            ]);
        }

        // Scenario 3: Manually Closed MPP
        Mpp::factory()->create([
            'plan_name' => "Canceled Operation Plan",
            'status' => \App\Enums\MppStatus::CLOSED,
        ]);

        // Scenario 4: Approved MPP with Ready to Publish RR
        $mpp4 = Mpp::factory()->create([
            'plan_name' => "Future Expansion",
            'status' => \App\Enums\MppStatus::APPROVED,
            'quota' => 5,
        ]);
        Rr::factory()->create([
            'mpp_id' => $mpp4->id,
            'status' => 'Ready to Publish',
            'quota' => 3,
        ]);

        // Scenario 5: Approved MPP with Ready to Publish RR
        $mpp5 = Mpp::factory()->create([
            'plan_name' => "Urgent Replacements",
            'status' => \App\Enums\MppStatus::APPROVED,
            'quota' => 2,
        ]);
        Rr::factory()->create([
            'mpp_id' => $mpp5->id,
            'status' => 'Ready to Publish',
            'quota' => 2,
        ]);

        // Scenario 6: Approved MPP with Active Published RR & Candidates in Progress
        $mpp6 = Mpp::factory()->create([
            'plan_name' => "Q3 Backend Engineering",
            'status' => \App\Enums\MppStatus::APPROVED,
            'quota' => 4,
            'department' => 'IT',
            'job_title' => 'Senior Backend Engineer',
        ]);
        $rr6 = Rr::factory()->create([
            'mpp_id' => $mpp6->id,
            'status' => 'Published',
            'job_title' => $mpp6->job_title,
            'department' => $mpp6->department,
            'quota' => 2,
        ]);
        $vac6 = Vacancy::factory()->create([
            'rr_id' => $rr6->id,
            'job_title' => $rr6->job_title,
            'department' => $rr6->department,
            'status' => 'Published',
            'quota' => 2,
        ]);
        
        // Generate Candidates for Vacancy 6 (In Progress)
        for ($i = 0; $i < 8; $i++) {
            $this->seedCandidateForVacancy($vac6, $applicants, $stagesArray, $activeUserIds, false);
        }

        // Scenario 7: Completed RR (Quota reached, Vacancy Closed)
        $mpp7 = Mpp::factory()->create([
            'plan_name' => "Q1 Marketing Campaign",
            'status' => \App\Enums\MppStatus::COMPLETED,
            'quota' => 3,
            'department' => 'Marketing',
            'job_title' => 'Social Media Specialist',
        ]);
        $rr7 = Rr::factory()->create([
            'mpp_id' => $mpp7->id,
            'status' => 'Completed',
            'job_title' => $mpp7->job_title,
            'department' => $mpp7->department,
            'quota' => 3,
        ]);
        $vac7 = Vacancy::factory()->create([
            'rr_id' => $rr7->id,
            'job_title' => $rr7->job_title,
            'department' => $rr7->department,
            'status' => 'Closed', // Completed vacancy is closed
            'quota' => 3,
        ]);
        
        // Generate 3 Hired Candidates
        for ($i = 0; $i < 3; $i++) {
            $this->seedCandidateForVacancy($vac7, $applicants, $stagesArray, $activeUserIds, true, \App\Enums\CandidateStatus::HIRED);
        }
        // Generate 2 Rejected Candidates
        for ($i = 0; $i < 2; $i++) {
            $this->seedCandidateForVacancy($vac7, $applicants, $stagesArray, $activeUserIds, true, \App\Enums\CandidateStatus::REJECTED);
        }

        // Scenario 8: Manually Closed RR
        $mpp8 = Mpp::factory()->create([
            'plan_name' => "Canceled Department",
            'status' => \App\Enums\MppStatus::CLOSED,
            'quota' => 1,
        ]);
        $rr8 = Rr::factory()->create([
            'mpp_id' => $mpp8->id,
            'status' => 'Closed', // Manually closed by HR
            'quota' => 1,
        ]);
        // If it was published before closed, it might have a closed vacancy and some candidates
        $vac8 = Vacancy::factory()->create([
            'rr_id' => $rr8->id,
            'status' => 'Closed',
            'quota' => 1,
        ]);
        for ($i = 0; $i < 2; $i++) {
            $this->seedCandidateForVacancy($vac8, $applicants, $stagesArray, $activeUserIds, true, \App\Enums\CandidateStatus::WITHDRAWN);
        }

        // 4. Seed Blacklist
        Blacklist::create([
            'name' => 'Spammer Name',
            'email' => 'spammer@fake.com',
            'phone' => '089988887777',
            'reason' => 'Mengirimkan puluhan lamaran fiktif dengan file CV yang rusak.',
        ]);

        Blacklist::create([
            'name' => 'Fraudulent Candidate',
            'email' => 'fraud@cheat.com',
            'phone' => '087766665555',
            'reason' => 'Memalsukan surat pengalaman kerja dan sertifikat kompetensi.',
        ]);
    }

    /**
     * Helper to reliably seed candidates with realistic movement history.
     */
    private function seedCandidateForVacancy($vacancy, $applicants, $stagesArray, &$activeUserIds, $isFinalized = false, $forcedStatus = null)
    {
        $candidate = Candidate::factory()->make([
            'vacancy_id' => $vacancy->id,
        ]);

        if ($isFinalized) {
            $maxStageIndex = 4; // Final stage
            $candidate->current_stage_id = $stagesArray[$maxStageIndex]->id;
            $candidate->status = $forcedStatus ?? \App\Enums\CandidateStatus::REJECTED;
        } else {
            $maxStageIndex = rand(0, count($stagesArray) - 1);
            $candidate->current_stage_id = $stagesArray[$maxStageIndex]->id;
            $candidate->status = $maxStageIndex === 4 
                ? (rand(0, 1) ? \App\Enums\CandidateStatus::HIRED : \App\Enums\CandidateStatus::REJECTED) 
                : \App\Enums\CandidateStatus::IN_PROGRESS;
        }

        $isActive = in_array($candidate->status, [\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::OFFERED]);

        // 70% chance to map to an actual User Account
        if (rand(1, 10) <= 7) {
            $availableUsers = $applicants;
            if ($isActive) {
                $availableUsers = $applicants->reject(function ($u) use ($activeUserIds) {
                    return in_array($u->id, $activeUserIds);
                });
            }

            if ($availableUsers->isNotEmpty()) {
                $user = $availableUsers->random();
                $candidate->user_id = $user->id;
                $candidate->name = $user->name;
                $candidate->email = $user->email;

                if ($isActive) {
                    $activeUserIds[] = $user->id;
                }
            }
        }

        if (!$candidate->user_id) {
            $candidate->name = "Kandidat {$this->candidateEmailCounter}";
            $candidate->email = "kandidat{$this->candidateEmailCounter}@example.org";
            $this->candidateEmailCounter++;
        }

        $candidate->save();

        if ($maxStageIndex > 0) {
            for ($i = 1; $i <= $maxStageIndex; $i++) {
                $currentLoopStage = $stagesArray[$i];
                
                CandidateMovement::create([
                    'candidate_id' => $candidate->id,
                    'from_stage_id' => $stagesArray[$i - 1]->id,
                    'to_stage_id' => $currentLoopStage->id,
                    'moved_at' => Carbon::now()->subDays(rand(1, 5) * (count($stagesArray) - $i)),
                    'interviewer_notes' => rand(0, 1) ? 'Kandidat menunjukkan performa yang sesuai dengan kriteria awal.' : null,
                ]);

                if ($currentLoopStage->needs_scorecard) {
                    $criteria = $currentLoopStage->scorecard_criteria ?: [
                        ['criteria' => 'Problem Solving & Logic', 'weight' => 40],
                        ['criteria' => 'Technical Knowledge', 'weight' => 40],
                        ['criteria' => 'Communication Skill', 'weight' => 20],
                    ];

                    foreach ($criteria as $k) {
                        Scorecard::create([
                            'candidate_id' => $candidate->id,
                            'stage_id' => $currentLoopStage->id,
                            'criteria' => $k['criteria'] ?? $k['name'] ?? '',
                            'weight' => $k['weight'],
                            'score' => rand(65, 95),
                        ]);
                    }
                }
            }
        }
    }
}
