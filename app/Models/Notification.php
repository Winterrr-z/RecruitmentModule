<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Notification
 *
 * Model Eloquent yang merepresentasikan notifikasi internal aplikasi untuk user HR.
 * Notifikasi ini digunakan untuk memberi tahu HR tentang aktivitas penting seperti:
 * - Lamaran kandidat baru (tipe: application_single, applications_bulk).
 * - Jadwal interview hari ini (tipe: interview).
 *
 * Tipe-tipe notifikasi:
 * - 'application_single' : Notifikasi individu untuk satu kandidat baru (real-time).
 * - 'applications_bulk'  : Notifikasi ringkasan/terakumulasi untuk banyak kandidat baru (stacked).
 * - 'interview'          : Notifikasi jadwal interview pada hari tersebut.
 *
 * Mekanisme pembuatan notifikasi:
 * 1. Otomatis via View Composer di AppServiceProvider (saat HR akses halaman pertama kali hari itu).
 * 2. Otomatis via model event Candidate::created (real-time, jika HR sudah login hari ini).
 * 3. Manual via command `php artisan notifications:generate`.
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property string $icon
 * @property int|null $candidate_id
 * @property int|null $interview_schedule_id
 * @property bool $is_read
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'candidate_id',
        'interview_schedule_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /** Relasi: User HR yang memiliki notifikasi ini. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi: Kandidat terkait (opsional, untuk navigasi dari notifikasi ke detail kandidat). */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /** Relasi: Jadwal interview terkait (opsional, untuk notifikasi tipe interview). */
    public function interviewSchedule(): BelongsTo
    {
        return $this->belongsTo(InterviewSchedule::class);
    }

    /**
     * Scope: Memfilter hanya notifikasi yang belum dibaca.
     * Digunakan di View Composer dan halaman notifikasi untuk menghitung badge.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Menandai notifikasi ini sebagai sudah dibaca.
     * Dipanggil saat user HR mengklik notifikasi.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
