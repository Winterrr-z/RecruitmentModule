<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class OfferingResponse
 *
 * Komponen Livewire untuk halaman publik di mana kandidat dapat merespons
 * (menerima atau menolak) Offering Letter yang dikirimkan oleh HR.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.guest')]
class OfferingResponse extends Component
{
    /** @var string Token unik untuk mengakses halaman offering ini. */
    public $token;

    /** @var \App\Models\Candidate|null Objek kandidat terkait. */
    public $candidate;

    /** @var string|null Menyimpan status balasan ('success_accept', 'success_reject', 'expired', 'invalid'). */
    public $statusResponse = null;

    /**
     * Memuat data penawaran berdasarkan token dari URL.
     * Juga mengecek apakah tautan penawaran sudah kedaluwarsa.
     *
     * @param string $token Token akses surat penawaran.
     */
    public function mount($token)
    {
        $this->token = $token;
        $this->candidate = Candidate::with('vacancy')->where('offering_token', $token)->first();

        if (!$this->candidate) {
            $this->statusResponse = 'invalid';
            return;
        }

        // Check if token is expired
        if ($this->candidate->offering_token_expires_at && $this->candidate->offering_token_expires_at->isPast()) {
            DB::transaction(function () {
                $this->candidate->update([
                    'status' => \App\Enums\CandidateStatus::EXPIRED,
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            });
            $this->statusResponse = 'expired';
        }
    }

    /**
     * Aksi Livewire ketika tombol Terima atau Tolak diklik secara asinkron.
     * 
     * @param string $choice Pilihan kandidat ('terima' atau 'tolak').
     */
    public function handleResponse($choice)
    {
        if (!$this->candidate) {
            $this->statusResponse = 'invalid';
            return;
        }

        // Re-verify expiration before action
        if ($this->candidate->offering_token_expires_at && $this->candidate->offering_token_expires_at->isPast()) {
            DB::transaction(function () {
                $this->candidate->update([
                    'status' => \App\Enums\CandidateStatus::EXPIRED,
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            });
            $this->statusResponse = 'expired';
            return;
        }

        $service = app(\App\Services\OfferingService::class);
        if ($choice === 'terima') {
            $service->acceptOffering($this->candidate);
        } else {
            $service->declineOffering($this->candidate);
        }
        $this->statusResponse = $choice === 'terima' ? 'success_accept' : 'success_reject';
    }

    /**
     * Aksi Controller (Standar HTTP POST) jika form disubmit secara konvensional.
     * 
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function respond(Request $request, $token)
    {
        $candidate = Candidate::where('offering_token', $token)->first();

        if (!$candidate) {
            return redirect()->route('offering.response', ['token' => $token]);
        }

        if ($candidate->offering_token_expires_at && $candidate->offering_token_expires_at->isPast()) {
            DB::transaction(function () use ($candidate) {
                $candidate->update([
                    'status' => \App\Enums\CandidateStatus::EXPIRED,
                    'offering_token' => null,
                    'offering_token_expires_at' => null,
                ]);
            });
            return redirect()->route('offering.response', ['token' => $token]);
        }

        $choice = $request->input('choice'); // 'terima' or 'tolak'
        if (!in_array($choice, ['terima', 'tolak'])) {
            return redirect()->back()->with('error', 'Pilihan tidak valid.');
        }

        $service = app(\App\Services\OfferingService::class);
        if ($choice === 'terima') {
            $service->acceptOffering($candidate);
        } else {
            $service->declineOffering($candidate);
        }

        return redirect()->route('offering.response', ['token' => $token])->with('status', $choice);
    }

    /**
     * Render antarmuka halaman respon offering.
     * Jika terjadi redirect dari POST, periksa session ('status') untuk memunculkan pesan sukses.
     */
    public function render()
    {
        // Menangkap status pesan dari sesi (jika memakai cara fallback POST biasa)
        if (session('status') === 'terima') {
            $this->statusResponse = 'success_accept';
        } elseif (session('status') === 'tolak') {
            $this->statusResponse = 'success_reject';
        }

        return view('livewire.ats.offering-response');
    }
}
