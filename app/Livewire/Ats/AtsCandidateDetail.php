<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Scorecard;
use Livewire\Component;

class AtsCandidateDetail extends Component
{
    public $candidateId;
    public $candidate;
    public $movements = [];
    public $schedules = [];
    public $scorecards = [];
    public $totalWeightedScore = 0;
    public $notes = [];

    public function mount($candidateId)
    {
        $this->candidateId = $candidateId;
        $this->candidate = Candidate::with('lowongan', 'currentStage')->findOrFail($candidateId);
        $this->movements = $this->candidate->candidateMovements()
            ->with('fromStage', 'toStage')
            ->orderBy('moved_at', 'desc')
            ->get();
        
        foreach ($this->movements as $movement) {
            $this->notes[$movement->id] = $movement->interviewer_notes;
        }
        
        $this->loadStageRequirements();
    }

    public function saveNote($movementId)
    {
        $movement = \App\Models\CandidateMovement::findOrFail($movementId);
        $movement->update(['interviewer_notes' => $this->notes[$movementId] ?? null]);
        session()->flash('message', 'Catatan berhasil disimpan.');
        
        // Refresh movements
        $this->movements = $this->candidate->candidateMovements()
            ->with('fromStage', 'toStage')
            ->orderBy('moved_at', 'desc')
            ->get();
    }

    public function loadStageRequirements()
    {
        $stageId = $this->candidate->current_stage_id;
        
        // Load interview schedules for this stage
        $this->schedules = InterviewSchedule::where('candidate_id', $this->candidateId)
            ->where('stage_id', $stageId)
            ->get();

        // Load scorecards for this stage
        $this->scorecards = Scorecard::where('candidate_id', $this->candidateId)
            ->where('stage_id', $stageId)
            ->get();

        // Calculate total weighted score: Σ(bobot * nilai) / 100
        if ($this->scorecards->isNotEmpty()) {
            $sumWeighted = $this->scorecards->sum(fn($s) => $s->bobot * $s->nilai);
            $this->totalWeightedScore = round($sumWeighted / 100, 2);
        } else {
            $this->totalWeightedScore = 0;
        }
    }

    public function downloadCv()
    {
        if ($this->candidate->cv_path && \Storage::disk('local')->exists($this->candidate->cv_path)) {
            return \Storage::disk('local')->download($this->candidate->cv_path);
        }
        session()->flash('error', 'File CV tidak ditemukan.');
    }

    public function downloadPortofolio()
    {
        if ($this->candidate->portofolio_path && \Storage::disk('local')->exists($this->candidate->portofolio_path)) {
            return \Storage::disk('local')->download($this->candidate->portofolio_path);
        }
        session()->flash('error', 'File Portofolio tidak ditemukan.');
    }

    public function render()
    {
        return view('livewire.ats.candidate-detail')->layout('layouts.app');
    }
}
