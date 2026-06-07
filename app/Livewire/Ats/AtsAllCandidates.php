<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Lowongan;
use Livewire\Component;
use Livewire\WithPagination;

class AtsAllCandidates extends Component
{
    use WithPagination;

    public $filterLowongan = '';
    public $filterStatus = '';
    public $filterStage = '';
    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterLowongan()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterStage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $lowongans = Lowongan::all();
        $stages = Stage::orderBy('sequence', 'asc')->get();

        $candidates = app(\App\Repositories\CandidateRepository::class)->getAllCandidates(
            $this->filterLowongan,
            $this->filterStatus,
            $this->filterStage,
            $this->search,
            15
        );

        return view('livewire.ats.ats-all-candidates', [
            'lowongans' => $lowongans,
            'stages' => $stages,
            'candidates' => $candidates,
        ])->layout('layouts.app');
    }
}
