<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    use HasFactory;

    protected $table = 'lowongans';

    protected $fillable = [
        'recruitment_request_id',
        'kuota',
        'jabatan',
        'departemen',
        'tipe_kerja',
        'lokasi',
        'application_deadline',
        'tampilkan_gaji',
        'estimasi_gaji_min',
        'estimasi_gaji_max',
        'deskripsi_pekerjaan',
        'spesifikasi_kebutuhan',
        'status',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'tampilkan_gaji' => 'boolean',
        'estimasi_gaji_min' => 'integer',
        'estimasi_gaji_max' => 'integer',
        'kuota' => 'integer',
        'status' => \App\Enums\LowonganStatus::class,
    ];

    public function recruitmentRequest(): BelongsTo
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_request_id');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'lowongan_id');
    }
}
