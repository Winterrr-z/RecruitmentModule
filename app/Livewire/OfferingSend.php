<?php

namespace App\Livewire;

use App\Models\Candidate;
use App\Mail\OfferingLetterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use illuminate\support\Facades\DB;
use Livewire\Component;
use livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OfferingSend extends Component
{
    public $candidateId;
    public $candidate;
    public $lowongan;

    public $isValid = false;
    public $errorMessage = '';

    public function mount($candidateId)
    {
        $this->candidateId = $candidateId;
        $this->candidate = Candidate::with(['lowongan', 'currentStage'])->findOrFail($candidateId);
        $this->lowongan = $this->candidate->lowongan;

        $this->validateCandidate();
    }

    /**
     * Validate candidate eligibility.
     */
    protected function validateCandidate()
    {
        // 1. Stage check (name: 'Final' or id: 2)
        $stage = $this->candidate->currentStage;
        if (!$stage || ($stage->id !== 2 && strtolower($stage->nama) !== 'final')) {
            $this->isValid = false;
            $this->errorMessage = 'Kandidat harus berada di stage "Final" untuk dikirimi offering letter.';
            return;
        }

        // 2. Status check (must be \App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, or \App\Enums\CandidateStatus::OFFERED)
        if (!in_array($this->candidate->status, [\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::OFFERED])) {
            $this->isValid = false;
            $this->errorMessage = 'Status kandidat tidak valid untuk dikirimi offering letter. Saat ini status kandidat adalah "' . $this->candidate->status . '".';
            return;
        }

        // 3. Lowongan check
        if (!$this->lowongan) {
            $this->isValid = false;
            $this->errorMessage = 'Data lowongan untuk kandidat ini tidak ditemukan.';
            return;
        }

        // 4. Quota check
        if ($this->lowongan->kuota <= 0) {
            $this->isValid = false;
            $this->errorMessage = 'Kuota lowongan untuk jabatan "' . $this->lowongan->jabatan . '" sudah habis.';
            return;
        }

        $this->isValid = true;
    }

    /**
     * Send Offering Letter action.
     */
    public function sendOffering()
    {
        $this->validateCandidate();

        if (!$this->isValid) {
            session()->flash('error', $this->errorMessage);
            return;
        }

        $token = hash_hmac('sha256', $this->candidate->id . now()->timestamp . Str::random(40), config('app.key'));
        $expiresAt = now()->addDays(3);

        DB::transaction(function () use ($token, $expiresAt) {
            $this->candidate->update([
                'status' => \App\Enums\CandidateStatus::OFFERED,
                'offering_token' => $token,
                'offering_token_expires_at' => $expiresAt,
            ]);
        });

        // Send email
        try {
            Mail::to($this->candidate->email)->send(new OfferingLetterMail($this->candidate, $this->lowongan, $token, $expiresAt));
            session()->flash('message', 'Offering letter telah dikirim ke email kandidat.');
        } catch (\Exception $e) {
            session()->flash('error', 'Kandidat berhasil di-update menjadi Offered, namun email gagal terkirim: ' . $e->getMessage());
        }

        return redirect()->route('ats.dashboard');
    }

    public function render()
    {
        return view('livewire.offering-send');
    }
}
