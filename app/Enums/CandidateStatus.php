<?php

namespace App\Enums;

enum CandidateStatus: string
{
    /**
     * Applied: Kandidat baru saja mengirimkan lamaran.
     * Kondisi: Otomatis diset saat melamar dari portal karir publik atau dibuat manual di stage pertama (Applied).
     */
    case APPLIED = 'Applied';

    /**
     * In Progress: Kandidat sedang aktif mengikuti proses seleksi.
     * Kondisi: Diset ketika kandidat dipindahkan ke tahapan seleksi pertengahan (Screening, Interview, Technical Test, dll.).
     */
    case IN_PROGRESS = 'In Progress';

    /**
     * Offered: Kandidat telah dikirimi surat penawaran kerja (Offering Letter).
     * Kondisi: Diset saat HR menekan tombol aksi "Hired" di pipeline (kandidat berpindah ke stage Final dengan status Offered)
     * atau saat HR mengirimkan offering letter secara manual dari halaman OfferingSend.
     */
    case OFFERED = 'Offered';

    /**
     * Hired: Kandidat menyetujui offering letter dan resmi bergabung.
     * Kondisi: Diset saat kandidat menekan tombol "Terima" pada halaman penawaran kerja (OfferingResponse).
     */
    case HIRED = 'Hired';

    /**
     * Expired: Batas waktu respon offering letter telah habis.
     * Kondisi: Otomatis diset oleh sistem/cron job jika pelamar tidak merespon dalam waktu 3 hari sejak token dibuat.
     */
    case EXPIRED = 'Expired';

    /**
     * Rejected: Kandidat dinyatakan tidak lolos seleksi oleh HR.
     * Kondisi: Diset saat HR menekan tombol aksi "Reject" di pipeline atau ketika otomatis ditolak (auto-reject)
     * karena kuota vacancy telah terpenuhi oleh kandidat lain yang di-hire.
     */
    case REJECTED = 'Rejected';

    /**
     * Withdrawn: Kandidat menarik diri/mengundurkan diri secara sukarela dari proses rekrutmen.
     * Kondisi: Diset ketika kandidat menolak penawaran kerja secara sukarela (menekan tombol "Tolak" di OfferingResponse)
     * atau ketika kandidat membatalkan lamarannya sebelum keputusan final dibuat.
     */
    case WITHDRAWN = 'Withdrawn';

    /**
     * Blacklisted: Kandidat diblokir dari proses rekrutmen di masa depan.
     * Kondisi: Diset saat HR memasukkan kandidat ke dalam daftar hitam karena pelanggaran berat atau kecurangan.
     */
    case BLACKLISTED = 'Blacklisted';
}
