<?php

namespace App\Enums;

/**
 * Enum RrStatus
 * 
 * Merepresentasikan status siklus hidup dari Recruitment Request (RR).
 */
enum RrStatus: string
{
    /**
     * Status draf/awal saat rekrutmen dibuat.
     * Siap untuk dipublikasikan namun belum ditayangkan di portal karir publik.
     * Pada status ini, data masih dapat diubah (edit) atau dihapus jika belum ada pelamar.
     */
    case READY_TO_PUBLISH = 'Ready to Publish';

    /**
     * Status aktif/siar.
     * Lowongan aktif dan ditayangkan secara publik di portal karir sehingga kandidat dapat melamar secara daring.
     */
    case PUBLISHED = 'Published';

    /**
     * Status selesai secara otomatis.
     * Terjadi ketika jumlah kandidat yang berstatus 'Hired' telah memenuhi kuota kebutuhan yang ditentukan.
     * Lowongan akan otomatis diturunkan dari portal karir.
     */
    case COMPLETED = 'Completed';

    /**
     * Status ditutup secara manual.
     * Dipicu secara manual oleh HR untuk menghentikan proses rekrutmen posisi ini sebelum kuota terpenuhi.
     * Lowongan akan diturunkan dari portal karir dan tidak dapat diaktifkan kembali.
     */
    case CLOSED = 'Closed';
}
