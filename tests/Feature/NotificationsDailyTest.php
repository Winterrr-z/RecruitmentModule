<?php

namespace Tests\Feature;

use App\Enums\CandidateStatus;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Notification;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Class NotificationsDailyTest
 *
 * Test suite untuk memverifikasi mekanisme notifikasi harian HR:
 *
 * 1. **Notifikasi Terakumulasi (Stacked) pada Login Pertama Hari Itu**:
 *    Ketika user HR pertama kali mengakses halaman HR di hari tersebut,
 *    sistem membuat satu notifikasi ringkasan untuk semua lamaran hari ini
 *    (tipe: applications_bulk) dan notifikasi individu untuk setiap jadwal
 *    interview hari ini (tipe: interview).
 *
 * 2. **Notifikasi Real-time saat Kandidat Baru Masuk**:
 *    Jika HR sudah login hari ini (cache key aktif), setiap kandidat baru
 *    yang melamar akan memicu notifikasi individu secara langsung
 *    (tipe: application_single).
 *
 * 3. **Kandidat Masuk Sebelum HR Login**:
 *    Jika HR belum login, kandidat baru TIDAK memicu notifikasi instan.
 *    Kandidat tersebut akan masuk ke dalam notifikasi stacked saat HR
 *    akhirnya login.
 *
 * @package Tests\Feature
 */
class NotificationsDailyTest extends TestCase
{
    use RefreshDatabase;

    private User $hrUser;
    private User $hrUser2;
    private Vacancy $vacancy;
    private Stage $stage;

    /**
     * Persiapan data dasar untuk setiap test case.
     * Membuat 2 user HR, stage default (Applied & Final), Mpp, Rr, dan 1 vacancy aktif.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Buat stage default yang diperlukan oleh Dashboard dan komponen lain
        $this->stage = Stage::firstOrCreate(
            ['id' => 1],
            ['name' => 'Applied', 'description' => 'Default applied stage', 'sequence' => 1]
        );
        Stage::firstOrCreate(
            ['id' => 2],
            ['name' => 'Final', 'description' => 'Default final stage', 'sequence' => 2]
        );

        $this->hrUser  = User::factory()->create(['role' => 'hr']);
        $this->hrUser2 = User::factory()->create(['role' => 'hr']);

        // Buat Mpp (Manpower Planning) sebagai parent dari Recruitment Request
        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'Plan IT',
            'department'           => 'IT',
            'job_title'            => 'Software Engineer',
            'quota'                => 5,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
            'status'               => 'Approved',
        ]);

        // Buat Recruitment Request (Rr) yang terhubung ke Mpp
        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Software Engineer',
            'department'           => 'IT',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'job_description'      => 'Deskripsi pekerjaan test',
            'job_requirements'     => 'Persyaratan pekerjaan test',
            'quota'                => 5,
            'status'               => 'Published',
        ]);

        // Buat Vacancy yang terhubung ke Rr
        $this->vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Software Engineer',
            'department'           => 'IT',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'job_description'      => 'Deskripsi pekerjaan test',
            'job_requirements'     => 'Persyaratan pekerjaan test',
            'status'               => 'Published',
            'quota'                => 5,
        ]);
    }

    /**
     * Helper: Membuat kandidat tanpa memicu model events.
     *
     * Menggunakan Candidate::withoutEvents() agar event `created` tidak terpicu,
     * sehingga notifikasi real-time tidak terbuat saat membuat data dummy.
     * Berguna untuk mengisolasi pengujian notifikasi stacked dari notifikasi real-time.
     *
     * @param array $attrs Atribut tambahan/override untuk kandidat
     * @return Candidate Instance kandidat yang baru dibuat
     */
    private function createCandidateQuietly(array $attrs = []): Candidate
    {
        return Candidate::withoutEvents(function () use ($attrs) {
            return Candidate::create(array_merge([
                'vacancy_id'       => $this->vacancy->id,
                'name'             => 'Test Candidate',
                'email'            => 'test' . rand(1000, 9999) . '@example.com',
                'phone'            => '081234567890',
                'status'           => CandidateStatus::APPLIED,
                'current_stage_id' => $this->stage->id,
                'source'           => 'public',
            ], $attrs));
        });
    }

    /**
     * Verifikasi: Akses halaman HR pertama kali hari ini membuat
     * notifikasi terakumulasi (stacked) untuk lamaran kandidat hari ini.
     */
    public function test_first_page_load_creates_stacked_candidate_notification(): void
    {
        // Buat 2 kandidat hari ini tanpa memicu event (simulasi data sudah ada sebelum HR login)
        $this->createCandidateQuietly(['name' => 'Andi']);
        $this->createCandidateQuietly(['name' => 'Budi']);

        // Pastikan cache belum ada dan notifikasi belum terbuat
        $today = Carbon::today()->toDateString();
        $cacheKey = "hr_daily_notif_{$this->hrUser->id}_{$today}";
        $this->assertFalse(Cache::has($cacheKey));

        // Aksi: HR mengakses dashboard (menggunakan layout layouts.hr)
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Verifikasi: Notifikasi stacked terbuat dengan jumlah kandidat yang benar
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'applications_bulk',
        ]);

        $notif = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'applications_bulk')
            ->first();

        $this->assertStringContainsString('2 kandidat baru', $notif->message);
    }

    /**
     * Verifikasi: Akses halaman HR kedua kalinya di hari yang sama
     * TIDAK membuat notifikasi duplikat (cache mencegah re-generation).
     */
    public function test_second_page_load_does_not_duplicate_notifications(): void
    {
        $this->createCandidateQuietly(['name' => 'Charlie']);

        // Akses pertama — notifikasi dibuat
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        $countAfterFirst = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'applications_bulk')
            ->count();

        // Akses kedua — notifikasi TIDAK boleh bertambah
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        $countAfterSecond = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'applications_bulk')
            ->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond);
    }

    /**
     * Verifikasi: Notifikasi jadwal interview hari ini terbuat
     * saat HR pertama kali mengakses halaman hari itu.
     */
    public function test_first_page_load_creates_interview_notifications(): void
    {
        $candidate = $this->createCandidateQuietly(['name' => 'Diana']);

        // Buat stage Interview dan jadwal interview untuk hari ini
        $interviewStage = Stage::firstOrCreate(
            ['name' => 'Interview'],
            ['description' => 'Interview stage', 'sequence' => 3, 'needs_schedule' => true, 'needs_scorecard' => false]
        );

        InterviewSchedule::create([
            'candidate_id' => $candidate->id,
            'stage_id'     => $interviewStage->id,
            'date'         => Carbon::today()->toDateString(),
            'time'         => '10:00',
            'location'     => 'Online',
        ]);

        // Aksi: HR mengakses dashboard
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Verifikasi: Notifikasi interview terbuat dengan candidate_id yang benar
        $this->assertDatabaseHas('notifications', [
            'user_id'      => $this->hrUser->id,
            'type'         => 'interview',
            'candidate_id' => $candidate->id,
        ]);
    }

    /**
     * Verifikasi: Kandidat baru yang masuk SETELAH HR login hari ini
     * memicu notifikasi real-time individu (application_single).
     */
    public function test_realtime_notification_for_logged_in_hr(): void
    {
        // Simulasi HR sudah login hari ini dengan mengakses halaman
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Buat kandidat baru (memicu model event `created`)
        $candidate = Candidate::create([
            'vacancy_id'       => $this->vacancy->id,
            'name'             => 'Eve Realtime',
            'email'            => 'eve@example.com',
            'phone'            => '081234567891',
            'status'           => CandidateStatus::APPLIED,
            'current_stage_id' => $this->stage->id,
            'source'           => 'public',
        ]);

        // Verifikasi: Notifikasi real-time individu terbuat
        $this->assertDatabaseHas('notifications', [
            'user_id'      => $this->hrUser->id,
            'type'         => 'application_single',
            'candidate_id' => $candidate->id,
        ]);

        // Verifikasi isi pesan notifikasi mengandung nama kandidat dan posisi
        $notif = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'application_single')
            ->where('candidate_id', $candidate->id)
            ->first();

        $this->assertStringContainsString('Eve Realtime', $notif->message);
        $this->assertStringContainsString('Software Engineer', $notif->message);
    }

    /**
     * Verifikasi: Jika HR memiliki notifikasi kandidat yang belum dibaca,
     * kandidat baru berikutnya akan mengubah notifikasi tersebut menjadi stacked (bulk).
     */
    public function test_realtime_notification_stacks_if_unread(): void
    {
        // Simulasi HR sudah login hari ini
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Buat kandidat pertama (membuat application_single)
        $candidate1 = Candidate::create([
            'vacancy_id'       => $this->vacancy->id,
            'name'             => 'Kandidat Pertama',
            'email'            => 'first@example.com',
            'phone'            => '081234567891',
            'status'           => CandidateStatus::APPLIED,
            'current_stage_id' => $this->stage->id,
            'source'           => 'public',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'application_single',
        ]);

        // Buat kandidat kedua (karena yang pertama belum dibaca, harusnya jadi applications_bulk)
        $candidate2 = Candidate::create([
            'vacancy_id'       => $this->vacancy->id,
            'name'             => 'Kandidat Kedua',
            'email'            => 'second@example.com',
            'phone'            => '081234567892',
            'status'           => CandidateStatus::APPLIED,
            'current_stage_id' => $this->stage->id,
            'source'           => 'public',
        ]);

        // Verifikasi: type application_single berubah menjadi applications_bulk
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'application_single',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'applications_bulk',
        ]);

        $notif = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'applications_bulk')
            ->first();

        // 2 kandidat baru (atau lebih, tergantung yang ada di database)
        $this->assertStringContainsString('2 kandidat baru apply hari ini', $notif->message);
    }

    /**
     * Verifikasi: Kandidat baru yang masuk TIDAK memicu notifikasi real-time
     * untuk HR yang belum login hari ini (tidak ada cache key).
     */
    public function test_no_realtime_notification_for_not_logged_in_hr(): void
    {
        // hrUser2 belum mengakses halaman HR hari ini — cache key belum ada
        $today = Carbon::today()->toDateString();
        $cacheKey = "hr_daily_notif_{$this->hrUser2->id}_{$today}";
        $this->assertFalse(Cache::has($cacheKey));

        // Buat kandidat baru (memicu model event)
        $candidate = Candidate::create([
            'vacancy_id'       => $this->vacancy->id,
            'name'             => 'Frank Offline',
            'email'            => 'frank@example.com',
            'phone'            => '081234567892',
            'status'           => CandidateStatus::APPLIED,
            'current_stage_id' => $this->stage->id,
            'source'           => 'public',
        ]);

        // Verifikasi: TIDAK ada notifikasi real-time untuk hrUser2
        $this->assertDatabaseMissing('notifications', [
            'user_id'      => $this->hrUser2->id,
            'type'         => 'application_single',
            'candidate_id' => $candidate->id,
        ]);
    }

    /**
     * Verifikasi: HR yang belum login saat kandidat masuk akan melihat
     * kandidat tersebut terakumulasi dalam notifikasi stacked saat login nanti.
     */
    public function test_offline_hr_sees_candidate_in_stacked_notification_on_login(): void
    {
        // Buat kandidat SEBELUM hrUser2 login (tanpa memicu event notifikasi)
        $this->createCandidateQuietly(['name' => 'Grace Stacked']);

        // Sekarang hrUser2 mengakses halaman untuk pertama kali hari ini
        $this->actingAs($this->hrUser2)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Verifikasi: Notifikasi stacked terbuat yang mencakup kandidat tersebut
        $notif = Notification::where('user_id', $this->hrUser2->id)
            ->where('type', 'applications_bulk')
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('1 kandidat baru', $notif->message);
    }

    /**
     * Verifikasi: Tidak ada notifikasi stacked yang terbuat jika
     * tidak ada lamaran kandidat maupun jadwal interview hari ini.
     */
    public function test_no_stacked_notification_when_nothing_today(): void
    {
        // HR mengakses halaman tanpa ada data kandidat atau interview hari ini
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Verifikasi: Tidak ada notifikasi tipe stacked
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'applications_bulk',
        ]);

        // Verifikasi: Tidak ada notifikasi tipe interview
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->hrUser->id,
            'type'    => 'interview',
        ]);
    }

    /**
     * Verifikasi: Setiap user HR mendapatkan notifikasi harian masing-masing
     * yang terisolasi satu sama lain (tidak berbagi notifikasi).
     */
    public function test_each_hr_user_gets_own_notifications(): void
    {
        $this->createCandidateQuietly(['name' => 'Henry']);

        // Kedua HR mengakses halaman
        $this->actingAs($this->hrUser)
            ->get(route('dashboard'))
            ->assertSuccessful();

        $this->actingAs($this->hrUser2)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Verifikasi: Masing-masing HR memiliki notifikasi stacked sendiri
        $notif1 = Notification::where('user_id', $this->hrUser->id)
            ->where('type', 'applications_bulk')
            ->count();

        $notif2 = Notification::where('user_id', $this->hrUser2->id)
            ->where('type', 'applications_bulk')
            ->count();

        $this->assertEquals(1, $notif1);
        $this->assertEquals(1, $notif2);
    }
}
