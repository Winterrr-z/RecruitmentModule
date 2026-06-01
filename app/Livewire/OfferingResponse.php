<?php

namespace App\Livewire;

use App\Models\Candidate;
use Illuminate\Http\Request;
use illuminate\Support\Facades\DB;
use Livewire\Component;

class OfferingResponse extends Component
{
    public $token;
    public $candidate;
    public $statusResponse = null; // 'success_accept', 'success_reject', 'expired', 'invalid'

    public function mount($token)
    {
        $this->token = $token;
        $this->candidate = Candidate::with('lowongan')->where('offering_token', $token)->first();

        if (!$this->candidate) {
            $this->statusResponse = 'invalid';
            return;
        }

        // Check if token is expired
        if ($this->candidate->offering_token_expires_at && $this->candidate->offering_token_expires_at->isPast()) {
            DB::transaction(function () {
                $this->candidate->update([
                    'status' => 'Expired',
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
                    'status' => 'Expired',
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
                    'status' => 'Expired',
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
                $candidate->status = 'Hired';
            } else {
                $candidate->status = 'Declined';
            }

            // Clear offering token fields
            $candidate->offering_token = null;
            $candidate->offering_token_expires_at = null;
            $candidate->save();

            // ONLY process lowongan/mpp completion if accepted
            if ($choice === 'terima') {
                $lowongan = $candidate->lowongan;
                if ($lowongan) {
                    $lowongan->kuota = max(0, $lowongan->kuota - 1);

                    if ($lowongan->kuota == 0) {
                        $lowongan->status = 'Completed/Closed';
                        $lowongan->save();
                        
                        $rr = $lowongan->recruitmentRequest;
                        if ($rr) {
                            $rr->status = 'Completed/Closed';
                            $rr->save();
                            
                            $mpp = $rr->mpp;
                            if ($mpp && $mpp->sisaKuota() <= 0) {
                                $mpp->status = 'Completed/Closed';
                                $mpp->save();
                            }
                        }
                    } else {
                        $lowongan->save();
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

        return view('livewire.offering-response');
    }
}
