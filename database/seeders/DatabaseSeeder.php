<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Mpp;
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
        $applicant1 = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'applicant',
        ]);

        $applicant2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'applicant',
        ]);

        $applicant3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob.johnson@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'applicant',
        ]);

        $applicant4 = User::create([
            'name' => 'David White',
            'email' => 'david.white@gmail.com',
            'password' => Hash::make('Password123'),
            'role' => 'applicant',
        ]);

        // 2. Seed Stages (Ensure Applied has id 1, and Final has id 2)
        $stageApplied = Stage::create([
            'id' => 1,
            'nama' => 'Applied',
            'deskripsi' => 'Kandidat baru saja melamar lowongan',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 1,
        ]);

        $stageFinal = Stage::create([
            'id' => 2,
            'nama' => 'Final',
            'deskripsi' => 'Tahap keputusan penawaran (offering)',
            'butuh_scorecard' => false,
            'butuh_jadwal' => false,
            'urutan' => 999,
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

        // 3. Seed MPPs
        $mpp1 = Mpp::create([
            'nama_plan' => 'Rencana Penambahan Backend Developer Q2',
            'departemen' => 'IT Department',
            'jabatan' => 'Backend Developer',
            'jumlah_kebutuhan' => 3,
            'estimasi_gaji_min' => 10000000,
            'estimasi_gaji_max' => 18000000,
            'syarat_pendidikan' => 'S1 Teknik Informatika',
            'syarat_pengalaman' => 'Minimal 3 tahun',
            'keahlian' => ['PHP', 'Laravel', 'MySQL', 'Redis'],
            'sla_bulan' => 3,
            'target_waktu_absolut' => now()->addMonths(3)->toDateString(),
            'status' => 'approved',
        ]);

        $mpp2 = Mpp::create([
            'nama_plan' => 'Rencana Penambahan Frontend Developer Q2',
            'departemen' => 'IT Department',
            'jabatan' => 'Frontend Developer',
            'jumlah_kebutuhan' => 2,
            'estimasi_gaji_min' => 9000000,
            'estimasi_gaji_max' => 15000000,
            'syarat_pendidikan' => 'S1/D3 Komputer',
            'syarat_pengalaman' => 'Minimal 2 tahun',
            'keahlian' => ['React', 'Vue', 'TailwindCSS', 'JavaScript'],
            'sla_bulan' => 2,
            'target_waktu_absolut' => now()->addMonths(2)->toDateString(),
            'status' => 'approved',
        ]);

        $mpp3 = Mpp::create([
            'nama_plan' => 'Rencana Desainer Grafis Baru',
            'departemen' => 'Design Department',
            'jabatan' => 'UI/UX Designer',
            'jumlah_kebutuhan' => 1,
            'estimasi_gaji_min' => 8000000,
            'estimasi_gaji_max' => 12000000,
            'syarat_pendidikan' => 'Semua Jurusan',
            'syarat_pengalaman' => 'Minimal 1 tahun',
            'keahlian' => ['Figma', 'Adobe Illustrator', 'Wireframing'],
            'sla_bulan' => 1,
            'target_waktu_absolut' => now()->addMonth()->toDateString(),
            'status' => 'draft',
        ]);

        $mpp4 = Mpp::create([
            'nama_plan' => 'Ekspansi Divisi HR',
            'departemen' => 'HR Department',
            'jabatan' => 'HR Specialist',
            'jumlah_kebutuhan' => 1,
            'estimasi_gaji_min' => 7000000,
            'estimasi_gaji_max' => 10000000,
            'syarat_pendidikan' => 'S1 Psikologi / Hukum',
            'syarat_pengalaman' => 'Minimal 2 tahun',
            'keahlian' => ['Recruitment', 'Industrial Relations', 'Payroll'],
            'sla_bulan' => 2,
            'target_waktu_absolut' => now()->subMonth()->toDateString(),
            'status' => 'completed',
        ]);

        // 4. Seed Lowongans (Recruitment Requests / RR)
        $lowongan1 = Lowongan::create([
            'mpp_id' => $mpp1->id,
            'jabatan' => $mpp1->jabatan,
            'departemen' => $mpp1->departemen,
            'estimasi_gaji_min' => $mpp1->estimasi_gaji_min,
            'estimasi_gaji_max' => $mpp1->estimasi_gaji_max,
            'expected_join_date' => $mpp1->target_waktu_absolut,
            'deskripsi_pekerjaan' => 'Membangun API handal, mengoptimalkan database, dan mendesain arsitektur server yang scalable.',
            'spesifikasi_kebutuhan' => "Keahlian mendalam di PHP & Laravel.\nPengalaman dengan Redis/Memcached.\nMemahami konsep microservices.",
            'tipe_kerja' => 'full-time',
            'lokasi' => 'on-site',
            'application_deadline' => now()->addDays(15)->toDateString(),
            'tampilkan_gaji' => true,
            'status' => 'Published',
            'kuota' => 3,
        ]);

        $lowongan2 = Lowongan::create([
            'mpp_id' => $mpp2->id,
            'jabatan' => $mpp2->jabatan,
            'departemen' => $mpp2->departemen,
            'estimasi_gaji_min' => $mpp2->estimasi_gaji_min,
            'estimasi_gaji_max' => $mpp2->estimasi_gaji_max,
            'expected_join_date' => $mpp2->target_waktu_absolut,
            'deskripsi_pekerjaan' => 'Mengembangkan antarmuka web interaktif menggunakan framework modern dan memastikan kecocokan desain.',
            'spesifikasi_kebutuhan' => "Familiar dengan React.js/Vue.js.\nMahir HTML5, CSS3, ES6+.\nPengalaman integrasi REST API.",
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => now()->addDays(10)->toDateString(),
            'tampilkan_gaji' => false,
            'status' => 'Published',
            'kuota' => 2,
        ]);

        $lowongan3 = Lowongan::create([
            'mpp_id' => $mpp4->id,
            'jabatan' => $mpp4->jabatan,
            'departemen' => $mpp4->departemen,
            'estimasi_gaji_min' => $mpp4->estimasi_gaji_min,
            'estimasi_gaji_max' => $mpp4->estimasi_gaji_max,
            'expected_join_date' => $mpp4->target_waktu_absolut,
            'deskripsi_pekerjaan' => 'Mengelola rekrutmen karyawan baru, administrasi kontrak, dan program pelatihan internal.',
            'spesifikasi_kebutuhan' => "Lulusan S1 Psikologi/Hukum.\nKomunikasi interpersonal yang sangat baik.\nTerbiasa menggunakan portal lowongan kerja.",
            'tipe_kerja' => 'contract',
            'lokasi' => 'on-site',
            'application_deadline' => now()->subDays(5)->toDateString(),
            'tampilkan_gaji' => true,
            'status' => 'Completed/Closed',
            'kuota' => 0,
        ]);

        // 5. Seed Candidates
        $candidate1 = Candidate::create([
            'lowongan_id' => $lowongan1->id,
            'user_id' => $applicant1->id,
            'nama' => $applicant1->name,
            'email' => $applicant1->email,
            'telepon' => '081211112222',
            'cv_path' => 'cvs/john_doe_cv.pdf',
            'portofolio_path' => 'portfolios/john_doe_portfolio.pdf',
            'current_stage_id' => $stageApplied->id,
            'status' => 'Applied',
            'source' => 'public',
        ]);

        $candidate2 = Candidate::create([
            'lowongan_id' => $lowongan1->id,
            'user_id' => $applicant2->id,
            'nama' => $applicant2->name,
            'email' => $applicant2->email,
            'telepon' => '081233334444',
            'cv_path' => 'cvs/jane_smith_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageApplied->id,
            'status' => 'Applied',
            'source' => 'public',
        ]);

        $candidate3 = Candidate::create([
            'lowongan_id' => $lowongan1->id,
            'user_id' => $applicant3->id,
            'nama' => $applicant3->name,
            'email' => $applicant3->email,
            'telepon' => '081255556666',
            'cv_path' => 'cvs/bob_johnson_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageInterview->id,
            'status' => 'Applied',
            'source' => 'public',
        ]);

        $candidate4 = Candidate::create([
            'lowongan_id' => $lowongan1->id,
            'user_id' => null,
            'nama' => 'Alice Brown',
            'email' => 'alice.brown@gmail.com',
            'telepon' => '081277778888',
            'cv_path' => 'cvs/alice_brown_cv.pdf',
            'portofolio_path' => 'portfolios/alice_brown_portfolio.pdf',
            'current_stage_id' => $stageTechnical->id,
            'status' => 'Applied',
            'source' => 'manual',
        ]);

        $candidate5 = Candidate::create([
            'lowongan_id' => $lowongan1->id,
            'user_id' => null,
            'nama' => 'Charlie Green',
            'email' => 'charlie.green@gmail.com',
            'telepon' => '081299990000',
            'cv_path' => 'cvs/charlie_green_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageFinal->id,
            'status' => 'Hired',
            'source' => 'public',
            'offering_token' => 'token_xyz_123_abc',
            'offering_token_expires_at' => now()->addDays(7),
        ]);

        $candidate6 = Candidate::create([
            'lowongan_id' => $lowongan2->id,
            'user_id' => $applicant4->id,
            'nama' => $applicant4->name,
            'email' => $applicant4->email,
            'telepon' => '082211112222',
            'cv_path' => 'cvs/david_white_cv.pdf',
            'portofolio_path' => 'portfolios/david_white_portfolio.pdf',
            'current_stage_id' => $stageApplied->id,
            'status' => 'Applied',
            'source' => 'public',
        ]);

        $candidate7 = Candidate::create([
            'lowongan_id' => $lowongan2->id,
            'user_id' => null,
            'nama' => 'Eva Black',
            'email' => 'eva.black@gmail.com',
            'telepon' => '082233334444',
            'cv_path' => 'cvs/eva_black_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageInterview->id,
            'status' => 'Applied',
            'source' => 'manual',
        ]);

        $candidate8 = Candidate::create([
            'lowongan_id' => $lowongan2->id,
            'user_id' => null,
            'nama' => 'Frank Blue',
            'email' => 'frank.blue@gmail.com',
            'telepon' => '082255556666',
            'cv_path' => 'cvs/frank_blue_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageFinal->id,
            'status' => 'Ditolak',
            'source' => 'public',
        ]);

        $candidate9 = Candidate::create([
            'lowongan_id' => $lowongan3->id,
            'user_id' => null,
            'nama' => 'Grace Yellow',
            'email' => 'grace.yellow@gmail.com',
            'telepon' => '083311112222',
            'cv_path' => 'cvs/grace_yellow_cv.pdf',
            'portofolio_path' => null,
            'current_stage_id' => $stageFinal->id,
            'status' => 'Hired',
            'source' => 'public',
        ]);

        // 6. Seed Candidate Movements
        // Candidate 3 movements
        CandidateMovement::create([
            'candidate_id' => $candidate3->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageScreening->id,
            'moved_at' => now()->subDays(2),
            'interviewer_notes' => 'CV and experience match Backend role.',
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate3->id,
            'from_stage_id' => $stageScreening->id,
            'to_stage_id' => $stageInterview->id,
            'moved_at' => now()->subDay(),
            'interviewer_notes' => 'Passed initial screening. Candidate scheduled for Interview.',
        ]);

        // Candidate 4 movements
        CandidateMovement::create([
            'candidate_id' => $candidate4->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageScreening->id,
            'moved_at' => now()->subDays(4),
            'interviewer_notes' => 'Manual add. Screening passed.',
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate4->id,
            'from_stage_id' => $stageScreening->id,
            'to_stage_id' => $stageInterview->id,
            'moved_at' => now()->subDays(3),
            'interviewer_notes' => 'Moving to interview phase.',
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate4->id,
            'from_stage_id' => $stageInterview->id,
            'to_stage_id' => $stageTechnical->id,
            'moved_at' => now()->subDays(2),
            'interviewer_notes' => 'Good communication. Moving to Technical Test stage.',
        ]);

        // Candidate 5 movements
        CandidateMovement::create([
            'candidate_id' => $candidate5->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageScreening->id,
            'moved_at' => now()->subDays(6),
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate5->id,
            'from_stage_id' => $stageScreening->id,
            'to_stage_id' => $stageInterview->id,
            'moved_at' => now()->subDays(5),
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate5->id,
            'from_stage_id' => $stageInterview->id,
            'to_stage_id' => $stageTechnical->id,
            'moved_at' => now()->subDays(4),
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate5->id,
            'from_stage_id' => $stageTechnical->id,
            'to_stage_id' => $stageFinal->id,
            'moved_at' => now()->subDays(2),
            'interviewer_notes' => 'Excellent results on coding test and interview.',
        ]);

        // Candidate 7 movements
        CandidateMovement::create([
            'candidate_id' => $candidate7->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageScreening->id,
            'moved_at' => now()->subDays(2),
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate7->id,
            'from_stage_id' => $stageScreening->id,
            'to_stage_id' => $stageInterview->id,
            'moved_at' => now()->subDay(),
        ]);

        // Candidate 8 movements
        CandidateMovement::create([
            'candidate_id' => $candidate8->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageScreening->id,
            'moved_at' => now()->subDays(3),
        ]);
        CandidateMovement::create([
            'candidate_id' => $candidate8->id,
            'from_stage_id' => $stageScreening->id,
            'to_stage_id' => $stageFinal->id,
            'moved_at' => now()->subDay(),
            'interviewer_notes' => 'Rejected after review.',
        ]);

        // Candidate 9 movements
        CandidateMovement::create([
            'candidate_id' => $candidate9->id,
            'from_stage_id' => $stageApplied->id,
            'to_stage_id' => $stageFinal->id,
            'moved_at' => now()->subDays(5),
        ]);

        // 7. Seed Interview Schedules
        // Candidate 3: Interview today
        InterviewSchedule::create([
            'candidate_id' => $candidate3->id,
            'stage_id' => $stageInterview->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '10:00:00',
            'tempat' => 'Ruang Meeting Lt. 2',
            'tautan_virtual' => 'https://zoom.us/j/123456789',
        ]);

        // Candidate 7: Interview today
        InterviewSchedule::create([
            'candidate_id' => $candidate7->id,
            'stage_id' => $stageInterview->id,
            'tanggal' => now()->toDateString(),
            'waktu' => '14:00:00',
            'tempat' => null,
            'tautan_virtual' => 'https://meet.google.com/eva-interview',
        ]);

        // Candidate 4: Interview was yesterday
        InterviewSchedule::create([
            'candidate_id' => $candidate4->id,
            'stage_id' => $stageInterview->id,
            'tanggal' => now()->subDay()->toDateString(),
            'waktu' => '09:00:00',
            'tempat' => 'Ruang Wawancara 1',
            'tautan_virtual' => null,
        ]);

        // Candidate 5: Interview was 5 days ago
        InterviewSchedule::create([
            'candidate_id' => $candidate5->id,
            'stage_id' => $stageInterview->id,
            'tanggal' => now()->subDays(5)->toDateString(),
            'waktu' => '11:00:00',
            'tempat' => null,
            'tautan_virtual' => 'https://zoom.us/j/987654321',
        ]);

        // 8. Seed Scorecards
        // Candidate 4 scorecard
        Scorecard::create([
            'candidate_id' => $candidate4->id,
            'stage_id' => $stageTechnical->id,
            'kriteria' => 'Logic & Algorithm',
            'bobot' => 50,
            'nilai' => 8,
        ]);
        Scorecard::create([
            'candidate_id' => $candidate4->id,
            'stage_id' => $stageTechnical->id,
            'kriteria' => 'PHP & OOP Practices',
            'bobot' => 50,
            'nilai' => 7,
        ]);

        // Candidate 5 scorecard
        Scorecard::create([
            'candidate_id' => $candidate5->id,
            'stage_id' => $stageTechnical->id,
            'kriteria' => 'Logic & Algorithm',
            'bobot' => 50,
            'nilai' => 9,
        ]);
        Scorecard::create([
            'candidate_id' => $candidate5->id,
            'stage_id' => $stageTechnical->id,
            'kriteria' => 'System Architecture',
            'bobot' => 50,
            'nilai' => 9,
        ]);

        // 9. Seed Blacklist
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
