<?php

namespace App\Enums;

/**
 * Enum MppProgressStatus
 * 
 * Merepresentasikan status progres dinamis (computed status) dari Manpower Planning (MPP)
 * yang dihitung berdasarkan Service Level Agreement (SLA) dan aktivitas perekrutan.
 */
enum MppProgressStatus: string
{
    /**
     * IN_PROGRESS:
     * Status default saat proses rekrutmen berjalan normal.
     * Kondisi: Waktu berjalan masih kurang dari atau sama dengan 50% SLA, dan sisa waktu pengerjaan lebih dari 2 bulan.
     */
    case IN_PROGRESS = 'In Progress';

    /**
     * NEED_ATTENTION:
     * Status peringatan awal yang menunjukkan proses membutuhkan perhatian.
     * Kondisi: Tidak ada aktivitas baru selama 7 hari atau lebih, ATAU waktu berjalan berada pada kisaran 51%-89% SLA dengan sisa waktu kurang dari atau sama dengan 30 hari.
     */
    case NEED_ATTENTION = 'Need Attention';

    /**
     * URGENT:
     * Status mendesak yang membutuhkan tindakan cepat.
     * Kondisi: Waktu berjalan sudah mencapai 90% SLA atau lebih, dan sisa waktu menuju target kurang dari 7 hari.
     */
    case URGENT = 'Urgent';

    /**
     * CRITICAL:
     * Status kritis yang menunjukkan batas waktu terlewati atau stagnasi parah.
     * Kondisi: Waktu berjalan sudah melebihi 100% SLA, ATAU tidak ada aktivitas baru sama sekali selama 14 hari berturut-turut atau lebih.
     */
    case CRITICAL = 'Critical';
}
