<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Mail\OfferingLetterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
class OfferingSend extends Component
{
    public $candidateId;
    public $candidate;
    public $vacancy;

    public $backUrl;
    public $backLabel;

    public $isValid = false;
    public $errorMessage = '';

    public function mount($candidateId)
    {
        $referer = request()->headers->get('referer');
        $from = request()->query('from');

        if ($from === 'candidates' || ($referer && str_contains($referer, '/ats/candidates'))) {
            $this->backUrl = route('ats.candidates');
            $this->backLabel = 'All Candidates';
        } else {
            $this->backUrl = route('ats.dashboard');
            $this->backLabel = 'Pipeline';
        }

        $this->candidateId = $candidateId;
        $this->candidate = Candidate::with(['vacancy', 'currentStage'])->findOrFail($candidateId);
        $this->vacancy = $this->candidate->vacancy;

        $this->validateCandidate();
    }

    /**
     * Validate candidate eligibility.
     */
    protected function validateCandidate()
    {
        // 1. Stage check (name: 'Final' or id: 2)
        $stage = $this->candidate->currentStage;
        if (!$stage || ($stage->id !== 2 && strtolower($stage->name) !== 'final')) {
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

        // 3. Vacancy check
        if (!$this->vacancy) {
            $this->isValid = false;
            $this->errorMessage = 'Data vacancy untuk kandidat ini tidak ditemukan.';
            return;
        }

        // 4. Quota check
        if ($this->vacancy->quota <= 0) {
            $this->isValid = false;
            $this->errorMessage = 'Kuota vacancy untuk jabatan "' . $this->vacancy->job_title . '" sudah habis.';
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
            Mail::to($this->candidate->email)->send(new OfferingLetterMail($this->candidate, $this->vacancy, $token, $expiresAt));
            session()->flash('message', 'Offering letter telah dikirim ke email kandidat.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email offering letter untuk kandidat {$this->candidate->id}: " . $e->getMessage());
            session()->flash('error', 'Kandidat berhasil di-update menjadi Offered, namun email gagal terkirim: ' . $e->getMessage());
        }

        return redirect()->route('ats.dashboard');
    }

    public function render()
    {
        return view('livewire.ats.offering-send');
    }
}
