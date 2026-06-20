<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Enums\CandidateStatus;
use Carbon\Carbon;

/**
 * Class AppServiceProvider
 *
 * Service provider utama aplikasi.
 * Bertanggung jawab untuk:
 * - Mendaftarkan komponen Blade layout (HR, Applicant, Auth, Guest).
 * - Mengendalikan lazy loading Eloquent di lingkungan non-produksi.
 * - Menyediakan variabel `$unreadNotifications` ke layout HR.
 * - Membuat notifikasi harian terakumulasi (stacked) pada akses pertama
 *   halaman HR di hari tersebut menggunakan mekanisme Cache.
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan service aplikasi.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap service aplikasi.
     *
     * Metode ini dijalankan saat boot framework dan melakukan:
     * 1. Mencegah lazy loading di lingkungan lokal/testing.
     * 2. Mendaftarkan alias komponen Blade untuk setiap layout.
     * 3. Mendaftarkan View Composer pada layout HR untuk:
     *    a. Membuat notifikasi harian terakumulasi saat akses pertama hari itu.
     *    b. Menghitung jumlah notifikasi belum dibaca untuk badge navbar.
     */
    public function boot(): void
    {
        // Cegah lazy loading di lingkungan non-produksi untuk mendeteksi N+1 query
        Model::preventLazyLoading(!app()->isProduction());

        // Daftarkan alias komponen Blade untuk setiap layout
        Blade::component('layouts.hr', 'app-layout');
        Blade::component('layouts.applicant', 'applicant-layout');
        Blade::component('layouts.auth', 'auth-layout');
        Blade::component('layouts.guest', 'guest-layout');

        /**
         * View Composer untuk layout HR.
         *
         * Dijalankan setiap kali view 'layouts.hr' dirender.
         * Fungsi utama:
         * 1. Membuat notifikasi harian terakumulasi (stacked) pada akses pertama
         *    halaman HR di hari tersebut, meliputi:
         *    - Satu notifikasi ringkasan untuk seluruh lamaran kandidat hari ini (tipe: applications_bulk).
         *    - Notifikasi individu untuk setiap jadwal interview hari ini (tipe: interview).
         * 2. Menghitung dan menyediakan jumlah notifikasi belum dibaca ($unreadNotifications)
         *    sebagai variabel yang di-share ke view untuk ditampilkan di badge navbar.
         *
         * Mekanisme pencegahan duplikasi:
         * - Menggunakan Cache dengan key format: "hr_daily_notif_{user_id}_{tanggal}"
         * - Cache berlaku hingga akhir hari (endOfDay), sehingga notifikasi hanya
         *   di-generate sekali per user per hari.
         */
        View::composer('layouts.hr', function ($view) {
            // Jika user belum login, set notifikasi ke 0 dan hentikan proses
            if (!Auth::check()) {
                $view->with('unreadNotifications', 0);
                return;
            }

            $user = Auth::user();

            // Hanya proses notifikasi harian untuk user dengan role HR
            if ($user->role === 'hr') {
                $today = Carbon::today();
                $cacheKey = "hr_daily_notif_{$user->id}_{$today->toDateString()}";

                // Cek apakah notifikasi harian sudah pernah di-generate hari ini
                if (!Cache::has($cacheKey)) {
                    // Hapus notifikasi harian yang sudah ada hari ini untuk menghindari duplikasi
                    // (berjaga-jaga jika cache terhapus tapi notifikasi sudah ada)
                    Notification::where('user_id', $user->id)
                        ->whereDate('created_at', $today)
                        ->whereIn('type', ['applications_bulk', 'interview'])
                        ->delete();

                    // --- 1. Notifikasi terakumulasi untuk lamaran kandidat hari ini ---
                    // Menghitung jumlah kandidat berstatus APPLIED yang dibuat hari ini
                    $todayApplicationsCount = Candidate::where('status', CandidateStatus::APPLIED)
                        ->whereDate('created_at', $today)
                        ->count();

                    // Buat satu notifikasi ringkasan jika ada lamaran masuk hari ini
                    if ($todayApplicationsCount > 0) {
                        Notification::create([
                            'user_id' => $user->id,
                            'type'    => 'applications_bulk',
                            'title'   => 'Aplikasi Kandidat Baru',
                            'message' => $todayApplicationsCount . ' kandidat baru apply hari ini',
                            'icon'    => 'people',
                        ]);
                    }

                    // --- 2. Notifikasi individu untuk jadwal interview hari ini ---
                    // Mengambil semua jadwal interview yang dijadwalkan hari ini
                    $todayInterviews = InterviewSchedule::whereDate('date', $today)
                        ->with('candidate', 'stage')
                        ->get();

                    // Buat notifikasi untuk setiap jadwal interview
                    foreach ($todayInterviews as $interview) {
                        Notification::create([
                            'user_id'               => $user->id,
                            'type'                  => 'interview',
                            'title'                 => 'Interview Hari Ini',
                            'message'               => $interview->candidate->name . ' - ' . $interview->stage->name . ' (' . $interview->time . ')',
                            'icon'                  => 'calendar_today',
                            'candidate_id'          => $interview->candidate_id,
                            'interview_schedule_id' => $interview->id,
                        ]);
                    }

                    // Simpan flag ke cache hingga akhir hari untuk mencegah pembuatan ulang
                    Cache::put($cacheKey, true, $today->copy()->endOfDay());
                }
            }

            // Hitung jumlah notifikasi belum dibaca dan kirim ke view
            $unreadNotifications = Notification::where('user_id', Auth::id())->unread()->count();
            $view->with('unreadNotifications', $unreadNotifications);
        });
    }
}
