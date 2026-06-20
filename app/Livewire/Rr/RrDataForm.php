<?php

namespace App\Livewire\Rr;

use Livewire\Form;

/**
 * Class RrDataForm
 *
 * Objek Form Livewire khusus untuk merangkum data dan aturan validasi 
 * yang diperlukan saat membuat atau mengubah Permintaan Rekrutmen (Recruitment Request).
 *
 * @package App\Livewire\Rr
 */
class RrDataForm extends Form
{
    /** @var string Judul publik dari lowongan pekerjaan. */
    public $title;

    /** @var string Penjelasan / deskripsi lengkap pekerjaan. */
    public $job_description;

    /** @var string Syarat dan kriteria pelamar (opsional). */
    public $job_requirements;

    /** @var string Jenis kontrak kerja (misal: 'full-time', 'contract'). */
    public $employment_type = 'full-time';

    /** @var string Lokasi/sistem kerja (misal: 'remote', 'on-site'). */
    public $location = 'remote';

    /** @var string Tanggal tenggat waktu pendaftaran pelamar. */
    public $application_deadline;

    /** @var bool Opsi untuk menampilkan rentang gaji ke publik. */
    public $show_salary = false;

    /** @var int Alokasi jumlah orang yang dibutuhkan (kuota). */
    public $quota;

    /**
     * Menjalankan validasi input formulir menggunakan batasan kuota maksimal yang dinamis 
     * (berdasarkan sisa kuota dari MPP yang bersangkutan).
     *
     * @param int $maxKuota Sisa kuota maksimal yang diperbolehkan oleh rencana tenaga kerja (MPP).
     * @return array
     */
    public function validateWithMaxQuota($maxKuota)
    {
        return $this->validate([
            'title' => 'required|string|max:150',
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'nullable|string|max:5000',
            'employment_type' => 'required|in:full-time,contract',
            'location' => 'required|in:remote,on-site',
            'application_deadline' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1|max:' . $maxKuota,
        ], [
            'title.required' => 'Judul RR wajib diisi.',
            'title.max' => 'Judul RR maksimal 150 karakter.',
            'job_description.required' => 'Deskripsi Pekerjaan wajib diisi.',
            'employment_type.required' => 'Tipe Kerja wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'application_deadline.required' => 'Application Deadline wajib diisi.',
            'application_deadline.after_or_equal' => 'Application Deadline minimal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota minimal 1.',
            'quota.max' => 'Kuota tidak boleh melebihi sisa kebutuhan MPP (' . $maxKuota . ').',
        ]);
    }
}
