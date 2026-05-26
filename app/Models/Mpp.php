<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Mpp
 * 
 * Model untuk merepresentasikan tabel 'mpps' (Manpower Planning).
 * Menyimpan data rencana kebutuhan tenaga kerja dari setiap departemen.
 *
 * @package App\Models
 * @property int $id
 * @property string $nama_plan
 * @property string $departemen
 * @property string $jabatan
 * @property int $jumlah_kebutuhan
 * @property int|null $estimasi_gaji_min
 * @property int|null $estimasi_gaji_max
 * @property int $sla_bulan
 * @property \Carbon\Carbon|null $target_waktu_absolut
 * @property string $status
 * @property string|null $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Mpp extends Model
{
    /**
     * @var string Nama tabel di database
     */
    protected $table = 'mpps';

    /**
     * @var array Kolom yang dapat diisi secara massal
     */
    protected $fillable = [
        'nama_plan',
        'departemen',
        'jabatan',
        'jumlah_kebutuhan',
        'estimasi_gaji_min',
        'estimasi_gaji_max',
        'sla_bulan',
        'target_waktu_absolut',
        'status',
        'note',
    ];

    /**
     * @var array Cast properti ke tipe data native
     */
    protected $casts = [
        'target_waktu_absolut' => 'date',
        'jumlah_kebutuhan' => 'integer',
        'estimasi_gaji_min' => 'integer',
        'estimasi_gaji_max' => 'integer',
        'sla_bulan' => 'integer',
    ];

    /**
     * Relasi ke tabel Lowongan.
     * Satu MPP dapat memiliki banyak Lowongan.
     *
     * @return HasMany
     */
    public function lowongans(): HasMany
    {
        return $this->hasMany(Lowongan::class, 'mpp_id');
    }
}
