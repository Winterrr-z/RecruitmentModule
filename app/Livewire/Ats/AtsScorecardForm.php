<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Scorecard;
use Livewire\Component;

class AtsScorecardForm extends Component
{
    public $candidateId;
    public $stageId;
    public $candidate;
    public $stage;

    // Evaluation list input fields: [['id' => null, 'kriteria' => '', 'bobot' => '', 'nilai' => '']]
    public $kriteriaList = [];

    // Weighted calculations
    public $totalBobot = 0;
    public $totalWeightedScore = 0;

    public function mount($candidateId, $stageId)
    {
        $this->candidateId = $candidateId;
        $this->stageId = $stageId;
        $this->candidate = Candidate::findOrFail($candidateId);
        $this->stage = Stage::findOrFail($stageId);

        // Load existing scorecards if they exist
        $existing = Scorecard::where('candidate_id', $candidateId)
            ->where('stage_id', $stageId)
            ->get();

        if ($existing->isNotEmpty()) {
            foreach ($existing as $scorecard) {
                $this->kriteriaList[] = [
                    'id' => $scorecard->id,
                    'kriteria' => $scorecard->kriteria,
                    'bobot' => $scorecard->bobot,
                    'nilai' => $scorecard->nilai,
                ];
            }
        } else {
            // Load from stage template
            $template = $this->stage->scorecard_kriteria ?: [];
            foreach ($template as $item) {
                $this->kriteriaList[] = [
                    'id' => null,
                    'kriteria' => $item['kriteria'],
                    'bobot' => $item['bobot'],
                    'nilai' => 0, // default
                ];
            }
        }

        $this->calculateTotals();
    }

    /**
     * Re-calculate the sum of weights and weighted score
     * Weighted Score: Σ(bobot * nilai) / 100
     */
    public function calculateTotals()
    {
        $this->totalBobot = 0;
        $weightedSum = 0;

        foreach ($this->kriteriaList as $item) {
            $bobot = (int)($item['bobot'] ?? 0);
            $nilai = (int)($item['nilai'] ?? 0);

            $this->totalBobot += $bobot;
            $weightedSum += ($bobot * $nilai);
        }

        $this->totalWeightedScore = $this->totalBobot > 0 ? round($weightedSum / 100, 2) : 0;
    }

    /**
     * Listening hook when input models change to trigger real-time calculations.
     */
    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'kriteriaList')) {
            $this->calculateTotals();
        }
    }

    public function save()
    {
        // 1. Predefined kriteria check
        if (empty($this->kriteriaList)) {
            $this->addError('kriteriaList', 'Tidak ada kriteria penilaian yang dikonfigurasi untuk stage ini.');
            return;
        }

        // 2. Validate scores (nilai must be between 1 and 10)
        foreach ($this->kriteriaList as $index => $item) {
            if (!isset($item['nilai']) || $item['nilai'] === '' || (int)$item['nilai'] < 1 || (int)$item['nilai'] > 10) {
                $this->addError("kriteriaList.{$index}.nilai", 'Nilai harus berkisar antara 1-10.');
                return;
            }
        }

        // Save atomically within a transaction block
        \DB::transaction(function () {
            // Delete old scorecard criteria for this candidate & stage
            Scorecard::where('candidate_id', $this->candidateId)
                ->where('stage_id', $this->stageId)
                ->delete();

            // Insert new evaluation rows
            foreach ($this->kriteriaList as $item) {
                Scorecard::create([
                    'candidate_id' => $this->candidateId,
                    'stage_id' => $this->stageId,
                    'kriteria' => trim($item['kriteria']),
                    'bobot' => (int)$item['bobot'],
                    'nilai' => (int)$item['nilai'],
                ]);
            }
        });

        session()->flash('message', 'Scorecard evaluasi berhasil disimpan.');

        return redirect()->route('ats.candidate.detail', ['candidateId' => $this->candidateId]);
    }

    public function render()
    {
        return view('livewire.ats.scorecard-form')->layout('layouts.app');
    }
}
