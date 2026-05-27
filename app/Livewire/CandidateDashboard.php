<?php

namespace App\Livewire;

use App\Models\Candidate;
use Livewire\Component;

/**
 * Class CandidateDashboard
 *
 * Halaman dashboard pelamar yang sudah login.
 * Menampilkan lamaran aktif dan lamaran tidak aktif (arsip/selesai)
 * milik user yang sedang login.
 *
 * Hanya dapat diakses oleh pengguna dengan role 'applicant'.
 *
 * @package App\Livewire
 */
class CandidateDashboard extends Component
{
    /**
     * Mapping nama stage ke ikon Material Symbols.
     *
     * @var array<string, string>
     */
    private const STAGE_ICONS = [
        'Applied'   => 'description',
        'Screening' => 'search',
        'Interview' => 'record_voice_over',
        'Final'     => 'fact_check',
    ];

    /**
     * Status yang dianggap tidak aktif (arsip / selesai).
     *
     * @var array<string>
     */
    private const INACTIVE_STATUSES = ['Ditolak', 'Hired', 'Offering Expired'];

    /**
     * Mendapatkan ikon Material Symbols berdasarkan nama stage.
     *
     * @param  string|null $stageName
     * @return string
     */
    public function getStageIcon(?string $stageName): string
    {
        return self::STAGE_ICONS[$stageName] ?? 'help';
    }

    /**
     * Render komponen.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $userId = auth()->id();

        // Lamaran aktif: belum ditolak, belum hired, belum expired
        $activeApplications = Candidate::where('user_id', $userId)
            ->whereNotIn('status', self::INACTIVE_STATUSES)
            ->with(['lowongan', 'currentStage'])
            ->latest()
            ->get();

        // Lamaran tidak aktif: arsip / selesai
        $inactiveApplications = Candidate::where('user_id', $userId)
            ->whereIn('status', self::INACTIVE_STATUSES)
            ->with(['lowongan', 'currentStage'])
            ->latest()
            ->get();

        return view('livewire.candidate-dashboard', compact('activeApplications', 'inactiveApplications'))
            ->layout('layouts.applicant');
    }
}
