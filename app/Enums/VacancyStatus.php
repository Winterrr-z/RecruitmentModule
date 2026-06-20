<?php

namespace App\Enums;

enum VacancyStatus: string
{
    /**
     * Draft: Lowongan kerja baru dibuat dan belum dipublikasikan.
     * Kondisi: Diset ketika HR membuat Recruitment Request (RR) atau lowongan namun menyimpannya sebagai draft.
     * Tampilan: Tidak akan terlihat di portal karir publik.
     */
    case DRAFT = 'Draft';

    /**
     * Published: Lowongan kerja sedang aktif dan menerima lamaran.
     * Kondisi: Diset ketika HR mempublikasikan lowongan.
     * Tampilan: Akan tampil di halaman karir publik (Careers) selama kuota > 0 dan belum melewati tenggat waktu (deadline).
     */
    case PUBLISHED = 'Published';

    /**
     * Completed: Lowongan kerja telah berhasil memenuhi kuota kandidat yang dibutuhkan.
     * Kondisi: Otomatis diset oleh sistem ketika kandidat yang berstatus "Hired" jumlahnya telah memenuhi batas kuota.
     * Tampilan: Tidak lagi menerima lamaran baru dan disembunyikan dari daftar lowongan aktif.
     */
    case COMPLETED = 'Completed';

    /**
     * Closed: Lowongan kerja ditutup sebelum kuota terpenuhi.
     * Kondisi: Diset secara manual oleh HR atau secara otomatis jika tenggat waktu (deadline) lamaran telah lewat.
     * Tampilan: Dihapus dari portal karir publik dan proses seleksi pelamar dihentikan.
     */
    case CLOSED = 'Closed';
}
