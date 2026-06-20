<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class GenerateNotifications
 *
 * Artisan command untuk membuat notifikasi harian secara manual via CLI.
 *
 * Perintah ini berfungsi sebagai alternatif/fallback dari mekanisme otomatis
 * yang ada di AppServiceProvider (View Composer). Berguna untuk:
 * - Menjalankan pembuatan notifikasi secara manual oleh admin.
 * - Dijalankan sebagai cron job (opsional) jika diperlukan.
 *
 * Catatan: Mekanisme utama pembuatan notifikasi harian sudah ditangani oleh
 * View Composer di AppServiceProvider yang terpicu secara otomatis saat HR
 * pertama kali mengakses halaman hari itu. Command ini hanya sebagai pelengkap.
 *
 * Alur kerja:
 * 1. Ambil semua user dengan role HR.
 * 2. Untuk setiap user HR:
 *    a. Hapus notifikasi hari ini yang sudah ada (mencegah duplikasi).
 *    b. Hitung kandidat baru hari ini:
 *       - Jika > 3: Buat satu notifikasi ringkasan (applications_bulk).
 *       - Jika <= 3: Buat notifikasi individu per kandidat (application_single).
 *    c. Buat notifikasi untuk setiap jadwal interview hari ini (interview).
 *
 * Penggunaan: php artisan notifications:generate
 *
 * @package App\Console\Commands
 */
class GenerateNotifications extends Command
{
    /** @var string Signature command untuk dipanggil via artisan. */
    protected $signature = 'notifications:generate';

    /** @var string Deskripsi command yang ditampilkan di `php artisan list`. */
    protected $description = 'Generate notifications untuk aplikasi candidate dan interview hari ini';

    /**
     * Menjalankan logika utama pembuatan notifikasi harian.
     *
     * Iterasi seluruh user HR, menghapus notifikasi lama hari ini,
     * lalu membuat notifikasi baru berdasarkan data kandidat dan interview hari ini.
     */
    public function handle()
    {
        $today = Carbon::today();
        $hrUsers = User::where('role', 'hr')->get();

        foreach ($hrUsers as $user) {
            // Hapus notifikasi hari ini yang sudah ada untuk user ini (menghindari duplikasi)
            Notification::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->delete();

            // --- 1. Notifikasi untuk kandidat yang melamar hari ini ---
            $todayApplications = Candidate::where('status', \App\Enums\CandidateStatus::APPLIED)
                ->whereDate('created_at', $today)
                ->get();

            // Jika lebih dari 3 lamaran, buat satu notifikasi ringkasan (stacked)
            if ($todayApplications->count() > 3) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'applications_bulk',
                    'title' => 'Aplikasi Kandidat Baru',
                    'message' => $todayApplications->count() . ' kandidat baru apply hari ini',
                    'icon' => 'people',
                ]);
            } else {
                // Jika 3 atau kurang, buat notifikasi individu per kandidat
                foreach ($todayApplications as $candidate) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'application_single',
                        'title' => 'Aplikasi Kandidat Baru',
                        'message' => $candidate->name . ' apply untuk posisi ' . ($candidate->vacancy?->job_title ?? 'N/A'),
                        'icon' => 'person_add',
                        'candidate_id' => $candidate->id,
                    ]);
                }
            }

            // --- 2. Notifikasi untuk jadwal interview hari ini ---
            $todayInterviews = InterviewSchedule::whereDate('date', $today)
                ->with('candidate', 'stage')
                ->get();

            // Buat notifikasi individu untuk setiap jadwal interview
            foreach ($todayInterviews as $interview) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'interview',
                    'title' => 'Interview Hari Ini',
                    'message' => $interview->candidate->name . ' - ' . $interview->stage->name . ' (' . $interview->time . ')',
                    'icon' => 'calendar_today',
                    'candidate_id' => $interview->candidate_id,
                    'interview_schedule_id' => $interview->id,
                ]);
            }
        }

        $this->info('Notifikasi berhasil di-generate untuk semua HR users');
    }
}
