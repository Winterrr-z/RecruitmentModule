<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class RecruitmentRequest extends Model
{
    use HasFactory;

    protected $table = 'recruitment_requests';

    protected $fillable = [
        'mpp_id',
        'kuota',
        'jabatan',
        'departemen',
        'estimasi_gaji_min',
        'estimasi_gaji_max',
        'expected_join_date',
        'deskripsi_pekerjaan',
        'spesifikasi_kebutuhan',
        'tipe_kerja',
        'lokasi',
        'application_deadline',
        'tampilkan_gaji',
        'status',
    ];

    protected $casts = [
        'expected_join_date' => 'date',
        'application_deadline' => 'date',
        'tampilkan_gaji' => 'boolean',
        'estimasi_gaji_min' => 'integer',
        'estimasi_gaji_max' => 'integer',
        'kuota' => 'integer',
    ];

    public function mpp(): BelongsTo
    {
        return $this->belongsTo(Mpp::class, 'mpp_id');
    }

    public function lowongan(): HasOne
    {
        return $this->hasOne(Lowongan::class, 'recruitment_request_id');
    }

    public function candidates(): HasManyThrough
    {
        return $this->hasManyThrough(
            Candidate::class,
            Lowongan::class,
            'recruitment_request_id',
            'lowongan_id',
            'id',
            'id'
        );
    }

    public function isActive(): bool
    {
        return !in_array($this->status, ['Completed/Closed']);
    }

    public function hiredCount(): int
    {
        return $this->lowongan?->candidates()->where('status', 'Hired')->count() ?? 0;
    }
}
