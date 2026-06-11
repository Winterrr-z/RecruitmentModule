<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class OfferingResponse extends Component
{
    public $token;
    public $candidate;
    public $statusResponse = null; // 'success_accept', 'success_reject', 'expired', 'invalid'

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
     * Livewire click action to accept/reject the offering.
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
     * Standard HTTP POST controller action.
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

    public function render()
    {
        // Capture session success status (e.g. from POST redirect)
        if (session('status') === 'terima') {
            $this->statusResponse = 'success_accept';
        } elseif (session('status') === 'tolak') {
            $this->statusResponse = 'success_reject';
        }

        return view('livewire.ats.offering-response');
    }
}
