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
    private const INACTIVE_STATUSES = ['Rejected', 'Hired', 'Declined', 'Expired'];

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
                    'status' => 'Expired',
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            });
            session()->flash('error', 'Waktu penawaran sudah habis.');
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($candidate, $choice) {
            if ($choice === 'terima') {
                $candidate->status = 'Hired';
            } else {
                $candidate->status = 'Declined';
            }

            // Hapus token setelah direpson
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();

            // Jika diterima, kurangi kuota lowongan
            if ($choice === 'terima') {
                $lowongan = $candidate->lowongan;
                if ($lowongan) {
                    $lowongan->kuota = max(0, $lowongan->kuota - 1);

                    if ($lowongan->kuota == 0) {
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
                            ->whereIn('status', ['Applied', 'In Progress', 'Offered'])
                            ->where('id', '!=', $candidate->id)
                            ->get();
                            
                        foreach ($rejectedCandidates as $rejected) {
                            $rejected->status = 'Rejected';
                            $rejected->save();
                            $rejected->notify(new \App\Notifications\CandidateRejectedNotification($lowongan));
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
        $hiredApplications = $allInactive->where('status', 'Hired');
        $inactiveApplications = $allInactive->where('status', '!=', 'Hired');

        return view('livewire.cw.candidate-dashboard', compact('activeApplications', 'inactiveApplications', 'hiredApplications'))
            ->layout('layouts.applicant');
    }
}
