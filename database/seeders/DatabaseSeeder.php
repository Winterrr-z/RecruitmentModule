<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mpp;
use App\Models\RecruitmentRequest;
use App\Models\Lowongan;
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
        RecruitmentRequest::truncate();
        Lowongan::truncate();
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
            'password' => Hash::make('Hr12345'),
            'role' => 'hr',
        ]);

        // Applicant Users
        $applicants = User::factory()->count(10)->create(['role' => 'applicant']);

        // 2. Seed Stages (Ensure exact IDs needed by the application)
        $stageApplied = Stage::create([
            'id' => 1,
            'nama' => 'Applied',
            'deskripsi' => 'Kandidat baru saja melamar lowongan',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 1,
        ]);

        $stageScreening = Stage::create([
            'id' => 3,
            'nama' => 'Screening',
            'deskripsi' => 'Penyaringan berkas dan kualifikasi awal',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 2,
        ]);

        $stageInterview = Stage::create([
            'id' => 4,
            'nama' => 'Interview',
            'deskripsi' => 'Wawancara dengan HR atau User',
            'butuh_scorecard' => false,
            'butuh_jadwal' => true,
            'urutan' => 3,
        ]);

        $stageTechnical = Stage::create([
            'id' => 5,
            'nama' => 'Technical Test',
            'deskripsi' => 'Ujian kompetensi teknis',
            'butuh_scorecard' => true,
            'butuh_jadwal' => false,
            'urutan' => 4,
        ]);

        $stageFinal = Stage::create([
            'id' => 2,
            'nama' => 'Final',
            'deskripsi' => 'Tahap keputusan penawaran (offering)',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 999,
        ]);

        $stagesArray = [$stageApplied, $stageScreening, $stageInterview, $stageTechnical, $stageFinal];

        // 3. Seed MPPs, RRs, Lowongans, and Candidates
        Mpp::factory()->count(15)->create()->each(function ($mpp) use ($applicants, $stagesArray) {
            
            // Buat 1 atau 2 Recruitment Request untuk setiap MPP, dengan membagi kuota
            $rrsCount = rand(1, 2);
            $sisaKuota = $mpp->jumlah_kebutuhan;

            for ($i = 0; $i < $rrsCount; $i++) {
                if ($sisaKuota <= 0) break;

                $kuotaRR = rand(1, min(5, $sisaKuota));
                $sisaKuota -= $kuotaRR;

                $rr = RecruitmentRequest::factory()->create([
                    'mpp_id' => $mpp->id,
                    'kuota' => $kuotaRR,
                ]);

                // Jika RR dipublikasi atau closed, otomatis buat Lowongan
                if (in_array($rr->status, ['Published', 'Completed', 'Closed'])) {
                    $lowonganStatus = $rr->status === 'Published' ? 'Published' : $rr->status;

                    $lowongan = Lowongan::factory()->create([
                        'recruitment_request_id' => $rr->id,
                        'jabatan' => $mpp->jabatan,
                        'departemen' => $mpp->departemen,
                        'kuota' => $rr->kuota,
                        'status' => $lowonganStatus,
                    ]);

                    // Generate candidates for this lowongan
                    Candidate::factory()->count(rand(2, 8))->create([
                        'lowongan_id' => $lowongan->id,
                        'user_id' => function() use ($applicants) {
                            // 50% pelamar memiliki user account
                            return rand(0, 1) ? $applicants->random()->id : null;
                        }
                    ])->each(function ($candidate) use ($stagesArray) {
                        
                        // Secara acak majukan kandidat ke beberapa tahap
                        $maxStageIndex = rand(0, count($stagesArray) - 1);
                        
                        $candidate->update([
                            'current_stage_id' => $stagesArray[$maxStageIndex]->id,
                            'status' => $maxStageIndex === 4 ? rand(0, 1) ? 'Hired' : 'Rejected' : 'In Progress'
                        ]);

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
                                if ($currentLoopStage->butuh_scorecard) {
                                    $kriteriaList = [
                                        ['nama' => 'Problem Solving & Logic', 'bobot' => 40],
                                        ['nama' => 'Technical Knowledge', 'bobot' => 40],
                                        ['nama' => 'Communication Skill', 'bobot' => 20],
                                    ];

                                    foreach ($kriteriaList as $k) {
                                        Scorecard::create([
                                            'candidate_id' => $candidate->id,
                                            'stage_id' => $currentLoopStage->id,
                                            'kriteria' => $k['nama'],
                                            'bobot' => $k['bobot'],
                                            'nilai' => rand(65, 95), // Nilai acak antara 65 - 95
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
            'nama' => 'Spammer Name',
            'email' => 'spammer@fake.com',
            'telepon' => '089988887777',
            'alasan' => 'Mengirimkan puluhan lamaran fiktif dengan file CV yang rusak.',
        ]);

        Blacklist::create([
            'nama' => 'Fraudulent Candidate',
            'email' => 'fraud@cheat.com',
            'telepon' => '087766665555',
            'alasan' => 'Memalsukan surat pengalaman kerja dan sertifikat kompetensi.',
        ]);
    }
}
