<?php

namespace App\Enums;

/**
 * Enum MppStatus
 * 
 * Merepresentasikan status siklus hidup dari Manpower Planning (MPP).
 */
enum MppStatus: string
{
    /**
     * DRAFT:
     * Status awal saat perencanaan MPP baru dibuat dan masih dalam proses penyusunan/pengisian.
     * MPP pada status ini masih dapat diedit secara bebas dan belum dipublikasikan sebagai lowongan.
     */
    case DRAFT = 'Draft';

    /**
     * APPROVED:
     * Status ketika perencanaan MPP telah disetujui oleh HR Manager/Pihak Berwenang.
     * Pada status ini, Recruitment Request (RR) atau lowongan pekerjaan (Vacancy) baru dapat dibuat.
     */
    case APPROVED = 'Approved';

    /**
     * COMPLETED:
     * Status otomatis ketika jumlah kuota perencanaan MPP telah terpenuhi sepenuhnya.
     * Terjadi apabila jumlah pelamar yang berstatus 'Hired' pada vacancy/RR terkait sudah mencapai atau melebihi batas kuota MPP.
     */
    case COMPLETED = 'Completed';

    /**
     * CLOSED:
     * Status ketika perencanaan MPP ditutup secara manual (misal: pembatalan posisi dari manajemen)
     * sebelum kuota terpenuhi, atau setelah proses rekrutmen diputuskan selesai secara paksa.
     * Hanya dapat dipicu jika tidak ada Recruitment Request (RR) aktif yang sedang dipublikasikan.
     */
    case CLOSED = 'Closed';
}
