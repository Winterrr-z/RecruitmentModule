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

        $this->processCandidateResponse($this->candidate, $choice);
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

        $this->processCandidateResponse($candidate, $choice);

        return redirect()->route('offering.response', ['token' => $token])->with('status', $choice);
    }

    /**
     * Internal logic for candidate response DB update.
     */
    protected function processCandidateResponse($candidate, $choice)
    {
        DB::transaction(function () use ($candidate, $choice) {
            if ($choice === 'terima') {
                $candidate->status = \App\Enums\CandidateStatus::HIRED;
            } else {
                $candidate->status = \App\Enums\CandidateStatus::DECLINED;
            }

            // Clear offering token fields
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();

            // ONLY process vacancy/mpp completion if accepted
            if ($choice === 'terima') {
                // Gunakan Pessimistic Locking untuk mencegah race condition pada saat pengurangan kuota
                $vacancy = $candidate->vacancy()->lockForUpdate()->first();
                if ($vacancy) {
                    $vacancy->quota = max(0, $vacancy->quota - 1);

                    if ($vacancy->quota == 0) {
                        $vacancy->status = 'Closed';
                        $vacancy->save();

                        $rr = $vacancy->rr;
                        if ($rr) {
                            $rr->status = 'Completed';
                            $rr->save();

                            $mpp = $rr->mpp;
                            // Optionally, compute if MPP is completely filled and save it
                            if ($mpp && $mpp->isFilled()) {
                                $mpp->status = 'Completed';
                                $mpp->save();
                            }
                        }
                    } else {
                        $vacancy->save();
                    }
                }
            }
        });
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
