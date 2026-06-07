<?php

namespace App\Livewire\Cw;

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
    private const INACTIVE_STATUSES = [\App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::DECLINED, \App\Enums\CandidateStatus::EXPIRED];

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
     * Respon terhadap offering (terima/tolak) langsung dari dashboard.
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

        \Illuminate\Support\Facades\DB::transaction(function () use ($candidate, $choice) {
            if ($choice === 'terima') {
                $candidate->status = \App\Enums\CandidateStatus::HIRED;
            } else {
                $candidate->status = \App\Enums\CandidateStatus::DECLINED;
            }

            // Hapus token setelah direpson
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();

            // Jika diterima, kurangi kuota lowongan
            if ($choice === 'terima') {
                $lowongan = $candidate->lowongan;
                if ($lowongan) {
                    $lowongan->quota = max(0, $lowongan->quota - 1);

                    if ($lowongan->quota == 0) {
                        $lowongan->status = 'Closed';
                        
                        $rr = $lowongan->recruitmentRequest;
                        if ($rr) {
                            $rr->status = 'Completed';
                            $rr->save();
                            
                            $mpp = $rr->mpp;
                            if ($mpp && $mpp->sisaKuota() <= 0) {
                                // Status mpp diupdate ke Completed tapi getComputedStatus bisa mengaturnya, jadi tidak masalah, 
                                // kita set saja secara eksplisit untuk berjaga-jaga.
                                $mpp->status = 'Completed';
                                $mpp->save();
                            }
                        }

                        // Auto-Reject Kandidat Lain (In Progress / Applied)
                        $rejectedCandidates = \App\Models\Candidate::where('lowongan_id', $lowongan->id)
                            ->whereIn('status', [\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::OFFERED])
                            ->where('id', '!=', $candidate->id)
                            ->get();
                            
                        foreach ($rejectedCandidates as $rejected) {
                            $rejected->status = \App\Enums\CandidateStatus::REJECTED;
                            $rejected->save();
                            try {
                                $rejected->notify(new \App\Notifications\CandidateRejectedNotification($lowongan));
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Gagal mengirim email penolakan otomatis untuk kandidat {$rejected->id}: " . $e->getMessage());
                            }
                        }
                    }
                    
                    $lowongan->save();
                }
            }
        });

        session()->flash('success', $choice === 'terima' ? 'Selamat! Anda telah menerima penawaran pekerjaan ini.' : 'Anda telah menolak penawaran pekerjaan ini.');
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
            ->with(['lowongan', 'currentStage', 'interviewSchedules'])
            ->latest()
            ->get();

        // Semua lamaran tidak aktif: arsip / selesai
        $allInactive = Candidate::where('user_id', $userId)
            ->whereIn('status', self::INACTIVE_STATUSES)
            ->with(['lowongan', 'currentStage'])
            ->latest()
            ->get();

        // Pisahkan yang Hired
        $hiredApplications = $allInactive->where('status', \App\Enums\CandidateStatus::HIRED);
        $inactiveApplications = $allInactive->where('status', '!=', \App\Enums\CandidateStatus::HIRED);

        return view('livewire.cw.candidate-dashboard', compact('activeApplications', 'inactiveApplications', 'hiredApplications'))
            ->layout('layouts.applicant');
    }
}
