<?php

namespace App\Livewire\Cw;

use App\Models\Candidate;
use Livewire\Component;
use Livewire\Attributes\Layout;

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
#[Layout('layouts.applicant')]
class CandidateDashboard extends Component
{
    /** @var bool Status tampil/sembunyikan modal konfirmasi penolakan offering. */
    public $showRejectModal = false;

    /** @var string Nama kandidat yang dipilih untuk penolakan. */
    public $selectedRejectCandidateName = '';

    /** @var string Jabatan/pekerjaan yang akan ditolak oleh pelamar. */
    public $selectedRejectJobTitle = '';
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
    private const INACTIVE_STATUSES = [
        \App\Enums\CandidateStatus::REJECTED,
        \App\Enums\CandidateStatus::HIRED,
        \App\Enums\CandidateStatus::WITHDRAWN,
        \App\Enums\CandidateStatus::EXPIRED,
        \App\Enums\CandidateStatus::BLACKLISTED
    ];

    /**
     * Mendapatkan ikon Material Symbols berdasarkan nama stage.
     *
     * @param  string|null $stageName
     * @return string
     */
    public function getStageIcon(?string $stageName): string
    {
        return self::STAGE_ICONS[$stageName] ?? 'search'; // Default icon jika stage tidak ditemukan
    }

    /**
     * Respon terhadap offering (terima/tolak) langsung dari dashboard pelamar.
     *
     * @param int $candidateId ID dari lamaran kandidat.
     * @param string $choice Pilihan pelamar ('terima' atau 'tolak').
     */
    public function respondOffering($candidateId, $choice)
    {
        $candidate = Candidate::where('id', $candidateId)->where('user_id', auth()->id())->first();

        if (!$candidate || !$candidate->offering_token) {
            session()->flash('error', 'Penawaran tidak valid atau sudah kadaluarsa.');
            return;
        }

        if ($candidate->offering_token_expires_at && $candidate->offering_token_expires_at->isPast()) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($candidate) {
                $candidate->update([
                    'status' => \App\Enums\CandidateStatus::EXPIRED,
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            });
            session()->flash('error', 'Waktu penawaran sudah habis.');
            return;
        }

        $service = app(\App\Services\OfferingService::class);
        if ($choice === 'terima') {
            $service->acceptOffering($candidate);
        } else {
            $service->declineOffering($candidate);
        }

        session()->flash('success', $choice === 'terima' ? 'Selamat! Anda telah menerima penawaran pekerjaan ini.' : 'Anda telah menolak penawaran pekerjaan ini.');
    }

    /**
     * Membuka modal konfirmasi ketika pelamar ingin menolak sebuah offering.
     *
     * @param int $candidateId ID lamaran terkait.
     */
    public function showRejection($candidateId)
    {
        $candidate = Candidate::where('id', $candidateId)->where('user_id', auth()->id())->first();
        if ($candidate) {
            $this->selectedRejectCandidateName = $candidate->name;
            $this->selectedRejectJobTitle = $candidate->vacancy ? ($candidate->vacancy->title ?: $candidate->vacancy->job_title) : 'Posisi Pekerjaan';
            $this->showRejectModal = true;
        }
    }

    /**
     * Menutup modal penolakan offering.
     */
    public function closeRejectModal()
    {
        $this->showRejectModal = false;
    }

    /**
     * Render komponen.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Auto-associate manual candidates that have the same email but no user_id yet
        if ($user) {
            Candidate::where('email', $user->email)
                ->whereNull('user_id')
                ->update(['user_id' => $userId]);
        }

        // Lamaran aktif: belum ditolak, belum hired, belum expired
        $activeApplications = Candidate::where('user_id', $userId)
            ->whereNotIn('status', self::INACTIVE_STATUSES)
            ->with(['vacancy', 'currentStage', 'interviewSchedules'])
            ->latest()
            ->get();

        // Semua lamaran tidak aktif: arsip / selesai
        $allInactive = Candidate::where('user_id', $userId)
            ->whereIn('status', self::INACTIVE_STATUSES)
            ->with(['vacancy', 'currentStage'])
            ->latest()
            ->get();

        // Pisahkan yang Hired
        $hiredApplications = $allInactive->where('status', \App\Enums\CandidateStatus::HIRED);
        $inactiveApplications = $allInactive->where('status', '!=', \App\Enums\CandidateStatus::HIRED);

        return view('livewire.cw.candidate-dashboard', compact('activeApplications', 'inactiveApplications', 'hiredApplications'));
    }
}
