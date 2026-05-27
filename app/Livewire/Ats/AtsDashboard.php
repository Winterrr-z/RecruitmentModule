<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Lowongan;
use App\Models\CandidateMovement;
use App\Models\Blacklist;
use App\Models\Scorecard;
use App\Models\InterviewSchedule;
use Livewire\Component;
use Livewire\WithPagination;

class AtsDashboard extends Component
{
    use WithPagination;

    // Filters & States
    public $selectedLowonganId = null;
    public $selectedStageId = null;
    public $search = '';

    // Blacklist Modal States
    public $showBlacklistModal = false;
    public $blacklistCandidateId = null;
    public $blacklistAlasan = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'blacklistAlasan' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'blacklistAlasan.required' => 'Alasan blacklist wajib diisi.',
        'blacklistAlasan.min' => 'Alasan blacklist minimal 5 karakter.',
    ];

    public function mount($selectedLowonganId = null)
    {
        $this->selectedLowonganId = $selectedLowonganId;
        // Default: set selected stage to first stage by urutan
        $firstStage = Stage::orderBy('urutan', 'asc')->first();
        if ($firstStage) {
            $this->selectedStageId = $firstStage->id;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedLowonganId()
    {
        $this->resetPage();
    }

    public function updatedSelectedStageId()
    {
        $this->resetPage();
    }

    /**
     * Check if a candidate meets requirements for their current stage before moving.
     */
    private function validateCurrentStageRequirements($candidate)
    {
        $currentStage = $candidate->currentStage;
        if (!$currentStage) {
            return null;
        }

        if ($currentStage->butuh_scorecard) {
            $scorecardCount = Scorecard::where('candidate_id', $candidate->id)
                ->where('stage_id', $currentStage->id)
                ->count();
            if ($scorecardCount === 0) {
                return "Kandidat '{$candidate->nama}' tidak dapat dipindahkan karena tahap saat ini ('{$currentStage->nama}') membutuhkan scorecard yang belum diisi.";
            }
        }

        if ($currentStage->butuh_jadwal) {
            $scheduleCount = InterviewSchedule::where('candidate_id', $candidate->id)
                ->where('stage_id', $currentStage->id)
                ->count();
            if ($scheduleCount === 0) {
                return "Kandidat '{$candidate->nama}' tidak dapat dipindahkan karena tahap saat ini ('{$currentStage->nama}') membutuhkan jadwal interview yang belum dibuat.";
            }
        }

        return null;
    }

    /**
     * Move candidate to a specified stage.
     */
    public function moveCandidate($id, $toStageId)
    {
        $candidate = Candidate::findOrFail($id);
        $toStage = Stage::findOrFail($toStageId);

        // Check if attempting to move to same stage
        if ($candidate->current_stage_id == $toStageId) {
            return;
        }

        // Validate current stage scorecard/schedule requirements
        $validationError = $this->validateCurrentStageRequirements($candidate);
        if ($validationError) {
            session()->flash('error', $validationError);
            return;
        }

        \DB::transaction(function () use ($candidate, $toStage) {
            // Save movement history
            CandidateMovement::create([
                'candidate_id' => $candidate->id,
                'from_stage_id' => $candidate->current_stage_id,
                'to_stage_id' => $toStage->id,
                'moved_at' => now(),
            ]);

            // Update candidate current stage
            $candidate->update([
                'current_stage_id' => $toStage->id,
            ]);
        });

        session()->flash('message', "Kandidat '{$candidate->nama}' berhasil dipindahkan ke stage '{$toStage->nama}'.");
    }

    /**
     * Reject candidate (status = 'Ditolak').
     */
    public function reject($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->update([
            'status' => 'Ditolak',
        ]);

        session()->flash('message', "Kandidat '{$candidate->nama}' berhasil ditolak.");
    }

    /**
     * Confirm blacklist: set ID, reset inputs, open modal.
     */
    public function confirmBlacklist($id)
    {
        $this->resetValidation();
        $this->blacklistCandidateId = $id;
        $this->blacklistAlasan = '';
        $this->showBlacklistModal = true;
    }

    /**
     * Blacklist candidate: save to blacklist table, reject candidate, close modal.
     */
    public function blacklist()
    {
        $this->validate();

        $candidate = Candidate::findOrFail($this->blacklistCandidateId);

        \DB::transaction(function () use ($candidate) {
            // Create blacklist entry
            Blacklist::create([
                'nama' => $candidate->nama,
                'email' => $candidate->email,
                'telepon' => $candidate->telepon,
                'alasan' => $this->blacklistAlasan,
            ]);

            // Reject candidate
            $candidate->update([
                'status' => 'Ditolak',
            ]);
        });

        $this->showBlacklistModal = false;
        $this->blacklistCandidateId = null;
        $this->blacklistAlasan = '';

        session()->flash('message', "Kandidat '{$candidate->nama}' berhasil dimasukkan ke daftar hitam (blacklist).");
    }

    /**
     * Approve candidate: find next stage by urutan, validate, and move.
     */
    public function approve($id)
    {
        $candidate = Candidate::findOrFail($id);
        
        $validationError = $this->validateCurrentStageRequirements($candidate);
        if ($validationError) {
            session()->flash('error', $validationError);
            return;
        }

        $currentStage = $candidate->currentStage;
        
        // Find next stage in sequence (first stage with urutan > current stage urutan)
        $nextStage = Stage::where('urutan', '>', $currentStage->urutan)
            ->orderBy('urutan', 'asc')
            ->first();

        if (!$nextStage) {
            session()->flash('error', "Kandidat '{$candidate->nama}' sudah berada di stage paling akhir.");
            return;
        }

        \DB::transaction(function () use ($candidate, $currentStage, $nextStage) {
            // Save movement history
            CandidateMovement::create([
                'candidate_id' => $candidate->id,
                'from_stage_id' => $currentStage->id,
                'to_stage_id' => $nextStage->id,
                'moved_at' => now(),
            ]);

            // Update candidate current stage
            $candidate->update([
                'current_stage_id' => $nextStage->id,
            ]);
        });

        session()->flash('message', "Kandidat '{$candidate->nama}' berhasil dilanjutkan ke stage '{$nextStage->nama}'.");
    }

    public function selectStage($stageId)
    {
        $this->selectedStageId = $stageId;
        $this->resetPage();
    }

    public function render()
    {
        // 1. Get published or ready lowongans
        $lowongans = Lowongan::whereIn('status', ['Published', 'Ready to Publish'])->get();

        // 2. Get all stages
        $stages = Stage::orderBy('urutan', 'asc')->get();

        // 3. Compute dynamic candidate counts per stage based on filters (excluding selectedStageId filter so you see counts across all stages)
        $stageCounts = Candidate::when($this->selectedLowonganId, fn($q) => $q->where('lowongan_id', $this->selectedLowonganId))
            ->when($this->search, fn($q) => $q->where(fn($sq) => $sq->where('nama', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%')))
            ->selectRaw('current_stage_id, count(*) as count')
            ->groupBy('current_stage_id')
            ->pluck('count', 'current_stage_id')
            ->toArray();

        // 4. Query candidates
        $candidates = Candidate::with('lowongan', 'currentStage')
            ->when($this->selectedLowonganId, fn($q) => $q->where('lowongan_id', $this->selectedLowonganId))
            ->when($this->selectedStageId, fn($q) => $q->where('current_stage_id', $this->selectedStageId))
            ->when($this->search, fn($q) => $q->where(fn($sq) => $sq->where('nama', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%')))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.ats.dashboard', [
            'lowongans' => $lowongans,
            'stages' => $stages,
            'stageCounts' => $stageCounts,
            'candidates' => $candidates,
        ])->layout('layouts.app');
    }
}
