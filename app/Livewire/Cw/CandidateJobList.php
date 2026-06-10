<?php

namespace App\Livewire\Cw;

use App\Models\Vacancy;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.applicant')]
class CandidateJobList extends Component
{
    /** @var string Kata kunci pencarian. */
    public string $search = '';

    /** @var array<string> Filter departemen (checkbox). */
    public array $selectedDepartments = [];

    /** @var array<string> Filter tipe kerja (checkbox). */
    public array $selectedTypes = [];

    /** @var string Urutan tampilan: 'newest' | 'oldest'. */
    public string $sortBy = 'newest';

    /**
     * Reset semua filter ke default.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedDepartments',
            'selectedTypes',
            'sortBy',
        ]);
    }

    /**
     * Render daftar vacancy khusus pelamar login.
     */
    public function render()
    {
        $query = Vacancy::query()
            ->where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', Carbon::today());

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('job_title', 'like', '%' . $this->search . '%')
                  ->orWhere('department', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->selectedDepartments)) {
            $query->whereIn('department', $this->selectedDepartments);
        }

        if (!empty($this->selectedTypes)) {
            $query->whereIn('employment_type', $this->selectedTypes);
        }

        $direction = $this->sortBy === 'oldest' ? 'asc' : 'desc';
        $vacancies = $query->orderBy('created_at', $direction)->paginate(10);

        // Rekap jumlah per departemen untuk sidebar
        $departments = \Illuminate\Support\Facades\Cache::remember('vacancy_department_counts', 3600, function () {
            return Vacancy::query()
                ->where('status', 'Published')
                ->where('quota', '>', 0)
                ->where('application_deadline', '>=', Carbon::today())
                ->selectRaw('department, count(*) as total')
                ->groupBy('department')
                ->orderBy('department')
                ->pluck('total', 'department')
                ->toArray();
        });

        return view('livewire.cw.career-job-list-logged-in', compact('vacancies', 'departments'));
    }
}
