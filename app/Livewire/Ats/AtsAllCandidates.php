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
        $stages = Stage::orderBy('urutan', 'asc')->get();

        $candidates = Candidate::query()
            ->with(['lowongan', 'currentStage'])
            ->when($this->filterLowongan, fn($q) => $q->where('lowongan_id', $this->filterLowongan))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterStage, fn($q) => $q->where('current_stage_id', $this->filterStage))
            ->when($this->search, fn($q) => $q->where(function($sub) {
                $sub->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.ats.ats-all-candidates', [
            'lowongans' => $lowongans,
            'stages' => $stages,
            'candidates' => $candidates,
        ])->layout('layouts.app');
    }
}
