<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Vacancy;
use Livewire\Component;
use Livewire\WithPagination;

class AtsAllCandidates extends Component
{
    use WithPagination;

    public $filterVacancy = '';
    public $filterStatus = '';
    public $filterStage = '';
    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterVacancy()
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
        $vacancies = Vacancy::all();
        $stages = Stage::getAllCached();

        $candidates = app(\App\Repositories\CandidateRepository::class)->getAllCandidates(
            $this->filterVacancy,
            $this->filterStatus,
            $this->filterStage,
            $this->search,
            15
        );

        return view('livewire.ats.ats-all-candidates', [
            'vacancies' => $vacancies,
            'stages' => $stages,
            'candidates' => $candidates,
        ])->layout('layouts.hr');
    }
}
