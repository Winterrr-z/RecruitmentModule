<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Mpp
 * 
 * Model untuk merepresentasikan tabel 'mpps' (Manpower Planning).
 * Menyimpan data rencana kebutuhan tenaga kerja dari setiap departemen.
 * Mendukung sistem status dinamis berdasarkan waktu, aktivitas, dan fulfillment.
 *
 * @package App\Models
 * @property int $id
 * @property string $nama_plan
 * @property string $departemen
 * @property string $jabatan
 * @property int $jumlah_kebutuhan
 * @property int|null $estimasi_gaji_min
 * @property int|null $estimasi_gaji_max
 * @property int $sla_hari
 * @property \Carbon\Carbon|null $target_waktu_absolut
 * @property string $status
 * @property string|null $note
 * @property \Carbon\Carbon|null $last_activity_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Mpp extends Model
{
    protected $table = 'mpps';

    protected $fillable = [
        'nama_plan',
        'departemen',
        'jabatan',
        'jumlah_kebutuhan',
        'estimasi_gaji_min',
        'estimasi_gaji_max',
        'sla_hari',
        'target_waktu_absolut',
        'status',
        'note',
        'last_activity_at',
    ];

    protected $casts = [
        'target_waktu_absolut' => 'date',
        'jumlah_kebutuhan' => 'integer',
        'estimasi_gaji_min' => 'integer',
        'estimasi_gaji_max' => 'integer',
        'sla_hari' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function lowongans(): HasMany
    {
        return $this->hasMany(Lowongan::class, 'mpp_id');
    }

    // ─── Status Helpers ───────────────────────────────────────

    /**
     * Mendapatkan jumlah kandidat yang sudah Hired melalui RR/Lowongan.
     */
    public function getHiredCount(): int
    {
        return Candidate::whereIn('lowongan_id', $this->lowongans()->pluck('id'))
            ->where('status', 'Hired')
            ->count();
    }

    /**
     * Cek apakah kuota MPP sudah terpenuhi (Filled) melalui RR.
     */
    public function isFilled(): bool
    {
        return $this->getHiredCount() >= $this->jumlah_kebutuhan;
    }

    /**
     * Cek apakah MPP memiliki kandidat yang aktif.
     * Kandidat dianggap aktif jika statusnya BUKAN Rejected, Hired, atau Withdrawn.
     */
    public function hasActiveCandidates(): bool
    {
        return Candidate::whereIn('lowongan_id', $this->lowongans()->pluck('id'))
            ->whereNotIn('status', ['Rejected', 'Hired', 'Withdrawn'])
            ->exists();
    }

    /**
     * Cek apakah MPP memiliki Lowongan (RR) yang berstatus Published.
     */
    public function hasPublishedRr(): bool
    {
        return $this->lowongans()->where('status', 'Published')->exists();
    }

    /**
     * Mendapatkan tanggal aktivitas terakhir dari seluruh sumber terkait MPP.
     * Sumber: MPP updated_at, Lowongan created_at, Candidate created_at, CandidateMovement moved_at.
     */
    public function getLastActivityDate(): Carbon
    {
        $dates = collect();

        // MPP own last_activity_at or updated_at
        $dates->push($this->last_activity_at ?? $this->updated_at);

        // Latest lowongan created
        $latestLowongan = $this->lowongans()->max('created_at');
        if ($latestLowongan) {
            $dates->push(Carbon::parse($latestLowongan));
        }

        // Latest candidate created via lowongans
        $lowonganIds = $this->lowongans()->pluck('id');
        if ($lowonganIds->isNotEmpty()) {
            $latestCandidate = Candidate::whereIn('lowongan_id', $lowonganIds)->max('created_at');
            if ($latestCandidate) {
                $dates->push(Carbon::parse($latestCandidate));
            }

            // Latest candidate movement
            $candidateIds = Candidate::whereIn('lowongan_id', $lowonganIds)->pluck('id');
            if ($candidateIds->isNotEmpty()) {
                $latestMovement = CandidateMovement::whereIn('candidate_id', $candidateIds)->max('moved_at');
                if ($latestMovement) {
                    $dates->push(Carbon::parse($latestMovement));
                }
            }
        }

        return $dates->filter()->max() ?? $this->updated_at;
    }

    /**
     * Menghitung status dinamis MPP berdasarkan prioritas:
     * 1. Closed (eksplisit via tombol Tutup Plan)
     * 2. Filled (kuota terpenuhi via RR)
     * 3. Critical (>100% terjalani ATAU 2 minggu tanpa aktivitas)
     * 4. Urgent (>=90% terjalani ATAU <1 minggu deadline)
     * 5. Need Attention (1 minggu tanpa aktivitas ATAU 51-89% + <=1 bulan deadline)
     * 6. In Progress (default)
     */
    public function getComputedStatus(): string
    {
        // 1. Closed — status eksplisit dari tombol "Tutup Plan"
        if (strtolower($this->status) === 'closed') {
            return 'Closed';
        }

        // 2. Filled — kuota terpenuhi melalui RR
        if ($this->isFilled()) {
            return 'Filled';
        }

        // Hitung persentase waktu dan sisa hari
        $now = now();
        $created = Carbon::parse($this->created_at);
        $target = Carbon::parse($this->target_waktu_absolut);
        $totalDays = max(1, $created->diffInDays($target));
        $elapsedDays = $created->diffInDays($now);
        $percent = ($elapsedDays / $totalDays) * 100;
        $daysRemaining = $now->diffInDays($target, false); // negatif jika overdue

        // Hitung hari sejak aktivitas terakhir
        $lastActivity = $this->getLastActivityDate();
        $daysSinceActivity = $lastActivity->diffInDays($now);

        // 3. Critical — overdue (>100%) ATAU 2 minggu tanpa aktivitas
        if ($percent > 100 || $daysSinceActivity >= 14) {
            return 'Critical';
        }

        // 4. Urgent — >=90% terjalani ATAU <7 hari deadline
        if ($percent >= 90 || $daysRemaining < 7) {
            return 'Urgent';
        }

        // 5. Need Attention — 1 minggu tanpa aktivitas ATAU (51-89% DAN <=30 hari deadline)
        if ($daysSinceActivity >= 7) {
            return 'Need Attention';
        }
        if ($percent >= 51 && $percent <= 89 && $daysRemaining <= 30) {
            return 'Need Attention';
        }

        // 6. In Progress — default
        return 'In Progress';
    }

    /**
     * Mendapatkan badge info untuk rendering di view.
     * Mengembalikan array dengan key: label, color, bg, icon.
     */
    public function getStatusBadge(): array
    {
        $status = $this->getComputedStatus();

        return match ($status) {
            'In Progress' => [
                'label' => 'In Progress',
                'color' => 'text-blue-700',
                'bg' => 'bg-blue-100',
                'dotColor' => 'bg-blue-500',
                'icon' => 'sync',
            ],
            'Need Attention' => [
                'label' => 'Need Attention',
                'color' => 'text-yellow-800',
                'bg' => 'bg-yellow-100',
                'dotColor' => 'bg-yellow-500',
                'icon' => 'warning',
            ],
            'Urgent' => [
                'label' => 'Urgent',
                'color' => 'text-orange-800',
                'bg' => 'bg-orange-100',
                'dotColor' => 'bg-orange-500',
                'icon' => 'priority_high',
            ],
            'Critical' => [
                'label' => 'Critical',
                'color' => 'text-red-800',
                'bg' => 'bg-red-100',
                'dotColor' => 'bg-red-500',
                'icon' => 'error',
            ],
            'Closed' => [
                'label' => 'Closed',
                'color' => 'text-gray-700',
                'bg' => 'bg-gray-200',
                'dotColor' => 'bg-gray-500',
                'icon' => 'lock',
            ],
            'Filled' => [
                'label' => 'Filled',
                'color' => 'text-green-800',
                'bg' => 'bg-green-100',
                'dotColor' => 'bg-green-500',
                'icon' => 'check_circle',
            ],
            default => [
                'label' => 'Unknown',
                'color' => 'text-gray-600',
                'bg' => 'bg-gray-100',
                'dotColor' => 'bg-gray-400',
                'icon' => 'help',
            ],
        };
    }
}

