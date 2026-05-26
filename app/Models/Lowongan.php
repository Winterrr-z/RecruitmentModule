<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    protected $table = 'lowongans';

    protected $fillable = [
        'mpp_id',
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
        'kuota',
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

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'lowongan_id');
    }
}
