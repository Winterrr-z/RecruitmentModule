<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Class Candidate
 *
 * Model Eloquent yang merepresentasikan kandidat/pelamar dalam sistem rekrutmen.
 * Setiap kandidat terkait dengan satu vacancy (lowongan), satu user (akun pelamar),
 * dan satu stage (tahapan seleksi) yang sedang dijalani.
 *
 * Fitur model event:
 * - saved/deleted: Menghapus cache hitungan stage pada dashboard.
 * - created: Mengirim notifikasi real-time ke user HR yang sudah login hari ini.
 *   Kandidat yang masuk sebelum HR login akan terakumulasi dalam notifikasi
 *   stacked yang dibuat saat HR pertama kali mengakses halaman hari itu.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int|null $vacancy_id
 * @property int|null $user_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $cv_path
 * @property string|null $portofolio_path
 * @property int|null $current_stage_id
 * @property \App\Enums\CandidateStatus $status
 * @property string|null $source
 * @property string|null $offering_token
 * @property \Carbon\Carbon|null $offering_token_expires_at
 */
class Candidate extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'candidates';

    protected $fillable = [
        'vacancy_id',
        'user_id',
        'name',
        'email',
        'phone',
        'cv_path',
        'portofolio_path',
        'current_stage_id',
        'status',
        'source',
        'offering_token',
        'offering_token_expires_at',
    ];

    protected $casts = [
        'offering_token_expires_at' => 'datetime',
        'status' => \App\Enums\CandidateStatus::class,
        'vacancy_id' => 'integer',
        'user_id' => 'integer',
        'current_stage_id' => 'integer',
    ];

    /** Relasi: Vacancy (lowongan) yang dilamar oleh kandidat. */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class, 'vacancy_id');
    }

    /**
     * Model event hooks yang dijalankan setelah model di-boot.
     *
     * Event yang didaftarkan:
     * 1. saved   — Menghapus cache hitungan stage dashboard agar data selalu fresh.
     * 2. deleted — Sama seperti saved, menghapus cache hitungan stage.
     * 3. created — Mengirim notifikasi real-time ke user HR yang sudah login hari ini.
     *
     * Mekanisme notifikasi real-time (event created):
     * - Mengambil semua user dengan role 'hr'.
     * - Untuk setiap user HR, mengecek apakah cache key "hr_daily_notif_{id}_{tanggal}"
     *   sudah ada (menandakan HR tersebut sudah mengakses halaman hari ini).
     * - Jika sudah ada (HR sudah login), buat notifikasi individu bertipe 'application_single'
     *   langsung agar muncul secara real-time di navbar HR.
     * - Jika belum ada (HR belum login), tidak perlu buat notifikasi.
     *   Kandidat ini akan otomatis terakumulasi dalam notifikasi stacked ('applications_bulk')
     *   yang dibuat saat HR pertama kali mengakses halaman via View Composer di AppServiceProvider.
     */
    protected static function booted()
    {
        // Hapus cache hitungan stage saat kandidat disimpan (create/update)
        static::saved(function ($candidate) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_stage_counts');
        });

        // Hapus cache hitungan stage saat kandidat dihapus
        static::deleted(function ($candidate) {
            \Illuminate\Support\Facades\Cache::forget('dashboard_stage_counts');
        });

        // Notifikasi real-time untuk user HR yang sudah login hari ini
        static::created(function ($candidate) {
            // Abaikan kandidat yang ditambahkan manual oleh HR atau statusnya bukan APPLIED (misal dari seeder)
            if ($candidate->source !== 'public' || $candidate->status !== \App\Enums\CandidateStatus::APPLIED) {
                return;
            }

            $today = \Carbon\Carbon::today()->toDateString();
            $hrUsers = \App\Models\User::where('role', 'hr')->get();

            // Muat relasi vacancy untuk menghindari LazyLoadingViolationException
            $candidate->loadMissing('vacancy');

            foreach ($hrUsers as $hrUser) {
                $cacheKey = "hr_daily_notif_{$hrUser->id}_{$today}";

                // Hanya kirim/update notifikasi jika HR sudah login hari ini (cache key ada)
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    // Cek apakah ada notifikasi kandidat yang belum dibaca hari ini
                    $unreadNotif = \App\Models\Notification::where('user_id', $hrUser->id)
                        ->whereIn('type', ['application_single', 'applications_bulk'])
                        ->whereDate('created_at', \Carbon\Carbon::today())
                        ->where('is_read', false)
                        ->first();

                    if ($unreadNotif) {
                        // Jika ada yang belum dibaca, jadikan/pertahankan sebagai stacked (bulk)
                        $todayApplicationsCount = \App\Models\Candidate::where('status', \App\Enums\CandidateStatus::APPLIED)
                            ->whereDate('created_at', \Carbon\Carbon::today())
                            ->count();

                        $unreadNotif->update([
                            'type'         => 'applications_bulk',
                            'title'        => 'Aplikasi Kandidat Baru',
                            'message'      => $todayApplicationsCount . ' kandidat baru apply hari ini',
                            'icon'         => 'people',
                            'candidate_id' => null, // Hapus referensi ke kandidat tunggal
                        ]);
                    } else {
                        // Jika belum ada notifikasi (atau sudah dibaca semua), buat notifikasi single baru
                        \App\Models\Notification::create([
                            'user_id'      => $hrUser->id,
                            'type'         => 'application_single',
                            'title'        => 'Aplikasi Kandidat Baru',
                            'message'      => $candidate->name . ' apply untuk posisi ' . ($candidate->vacancy?->job_title ?? 'N/A'),
                            'icon'         => 'person_add',
                            'candidate_id' => $candidate->id,
                        ]);
                    }
                }
            }
        });
    }

    /** Relasi: User (akun pelamar) yang memiliki data kandidat ini. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Relasi: Tahapan seleksi (stage) yang sedang dijalani kandidat saat ini. */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'current_stage_id');
    }

    /** Relasi: Riwayat perpindahan tahapan seleksi kandidat. */
    public function candidateMovements(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'candidate_id');
    }

    /** Relasi: Alias dari candidateMovements untuk kemudahan akses. */
    public function movements(): HasMany
    {
        return $this->hasMany(CandidateMovement::class, 'candidate_id');
    }

    /** Relasi: Jadwal interview yang dimiliki kandidat. */
    public function interviewSchedules(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class, 'candidate_id');
    }

    /** Relasi: Data scorecard evaluasi kandidat. */
    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class, 'candidate_id');
    }
}
