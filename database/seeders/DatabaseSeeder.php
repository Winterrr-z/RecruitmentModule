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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key constraints to allow truncating
        Schema::disableForeignKeyConstraints();

        // Clear existing data
        User::truncate();
        Mpp::truncate();
        Rr::truncate();
        Vacancy::truncate();
        Stage::truncate();
        Candidate::truncate();
        CandidateMovement::truncate();
        InterviewSchedule::truncate();
        Scorecard::truncate();
        Blacklist::truncate();

        Schema::enableForeignKeyConstraints();

        // 1. Seed Users
        // HR User
        User::create([
            'name' => 'HR',
            'email' => 'hr@company.com',
            'password' => Hash::make('HrPassword'),
            'role' => 'hr',
        ]);

        // Applicant Users
        $applicants = collect();
        for ($i = 1; $i <= 10; $i++) {
            $applicants->push(User::create([
                'name' => "Kandidat {$i}",
                'email' => "kandidat{$i}@example.org",
                'password' => Hash::make('AppPassword'),
                'role' => 'applicant',
            ]));
        }

        // 2. Seed Stages (Ensure exact IDs needed by the application)
        $stageApplied = Stage::create([
            'id' => 1,
            'name' => 'Applied',
            'description' => 'Kandidat baru saja melamar vacancy',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 1,
        ]);

        $stageScreening = Stage::create([
            'id' => 3,
            'name' => 'Screening',
            'description' => 'Penyaringan berkas dan kualifikasi awal',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 2,
        ]);

        $stageInterview = Stage::create([
            'id' => 4,
            'name' => 'Interview',
            'description' => 'Wawancara dengan HR atau User',
            'needs_scorecard' => false,
            'needs_schedule' => true,
            'sequence' => 3,
        ]);

        $stageTechnical = Stage::create([
            'id' => 5,
            'name' => 'Technical Test',
            'description' => 'Ujian kompetensi teknis',
            'needs_scorecard' => true,
            'needs_schedule' => false,
            'sequence' => 4,
        ]);

        $stageFinal = Stage::create([
            'id' => 2,
            'name' => 'Final',
            'description' => 'Tahap keputusan penawaran (offering)',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 999,
        ]);

        $stagesArray = [$stageApplied, $stageScreening, $stageInterview, $stageTechnical, $stageFinal];

        $activeUserIds = [];

        // 3. Seed MPPs, RRs, Vacancies, and Candidates
        Mpp::factory()->count(15)->create()->each(function ($mpp) use ($applicants, $stagesArray, &$activeUserIds) {
            
            // Buat 1 atau 2 Recruitment Request untuk setiap MPP, dengan membagi kuota
            $rrsCount = rand(1, 2);
            $sisaKuota = $mpp->quota;

            for ($i = 0; $i < $rrsCount; $i++) {
                if ($sisaKuota <= 0) break;

                $kuotaRR = rand(1, min(5, $sisaKuota));
                $sisaKuota -= $kuotaRR;

                $rr = Rr::factory()->create([
                    'mpp_id' => $mpp->id,
                    'quota' => $kuotaRR,
                ]);

                // Jika RR dipublikasi atau closed, otomatis buat Vacancy
                if (in_array($rr->status->value, ['Published', 'Completed', 'Closed'])) {
                    $vacancyStatus = match ($rr->status) {
                        \App\Enums\RrStatus::PUBLISHED => \App\Enums\VacancyStatus::PUBLISHED,
                        \App\Enums\RrStatus::COMPLETED => \App\Enums\VacancyStatus::COMPLETED_CLOSED,
                        \App\Enums\RrStatus::CLOSED => \App\Enums\VacancyStatus::CLOSED,
                        default => \App\Enums\VacancyStatus::DRAFT,
                    };

                    $vacancy = Vacancy::factory()->create([
                        'rr_id' => $rr->id,
                        'job_title' => $mpp->job_title,
                        'department' => $mpp->department,
                        'quota' => $rr->quota,
                        'status' => $vacancyStatus,
                    ]);

                    // Generate candidates for this vacancy
                    Candidate::factory()->count(rand(2, 8))->create([
                        'vacancy_id' => $vacancy->id,
                    ])->each(function ($candidate) use ($applicants, $stagesArray, &$activeUserIds) {
                        // Secara acak majukan kandidat ke beberapa tahap
                        $maxStageIndex = rand(0, count($stagesArray) - 1);
                        
                        $candidate->current_stage_id = $stagesArray[$maxStageIndex]->id;
                        $candidate->status = $maxStageIndex === 4 ? (rand(0, 1) ? \App\Enums\CandidateStatus::HIRED : \App\Enums\CandidateStatus::REJECTED) : \App\Enums\CandidateStatus::IN_PROGRESS;

                        $isActive = in_array($candidate->status, [\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::OFFERED]);

                        // 50% pelamar memiliki user account
                        if (rand(0, 1)) {
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

                        $candidate->save();

                        if ($maxStageIndex > 0) {
                            for ($i = 1; $i <= $maxStageIndex; $i++) {
                                $currentLoopStage = $stagesArray[$i];
                                
                                CandidateMovement::create([
                                    'candidate_id' => $candidate->id,
                                    'from_stage_id' => $stagesArray[$i - 1]->id,
                                    'to_stage_id' => $currentLoopStage->id,
                                    'moved_at' => now()->subDays(rand(1, 5) * (count($stagesArray) - $i)),
                                    'interviewer_notes' => rand(0, 1) ? 'Catatan hasil tahapan yang baik' : null,
                                ]);

                                // Jika stage ini butuh scorecard, buatkan draf / hasil penilaiannya
                                if ($currentLoopStage->needs_scorecard) {
                                    $kriteriaList = [
                                        ['name' => 'Problem Solving & Logic', 'weight' => 40],
                                        ['name' => 'Technical Knowledge', 'weight' => 40],
                                        ['name' => 'Communication Skill', 'weight' => 20],
                                    ];

                                    foreach ($kriteriaList as $k) {
                                        Scorecard::create([
                                            'candidate_id' => $candidate->id,
                                            'stage_id' => $currentLoopStage->id,
                                            'criteria' => $k['name'],
                                            'weight' => $k['weight'],
                                            'score' => rand(65, 95), // Nilai acak antara 65 - 95
                                        ]);
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });

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
}
