<?php

namespace App\Livewire\Hr;

use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Stage;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
class DashboardIndex extends Component
{
    // Metric counts
    public int $activeVacancyCount = 0;
    public int $newCandidateCount = 0;
    public int $todayInterviewCount = 0;

    // Donut Carousel
    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Vacancy[] */
    public $activeVacancies;
    public int $currentVacancyIndex = 0;

    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Stage[] */
    public $stages;

    // Calendar
    public int $currentMonth;
    public int $currentYear;
    public string $monthName = '';
    public array $calendarGrid = [];

    // Global Bar Chart
    public array $barChartLabels = [];
    public array $barChartValues = [];

    public function mount()
    {
        Carbon::setLocale('id');
        
        $this->currentMonth = (int) now()->format('m');
        $this->currentYear = (int) now()->format('Y');

        $this->loadData();
    }

    public function loadData()
    {
        // 1. Widget 1: Active vacancy count
        $this->activeVacancyCount = Vacancy::where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->count();

        // 4. Load stages first
        $this->stages = Stage::getAllCached();

        // 2. Widget 2: New candidate count (Applied stage, no movements)
        $firstStageId = $this->stages->where('is_first_stage', true)->first()?->id ?? 1;
        $this->newCandidateCount = Candidate::where('current_stage_id', $firstStageId)
            ->whereDoesntHave('movements')
            ->count();

        // 3. Widget 3: Today's interviews count
        $this->todayInterviewCount = InterviewSchedule::whereDate('date', today())->count();

        // 5. Load active vacancies for Donut Carousel
        $this->activeVacancies = Vacancy::where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->get();

        // 6. Load calendar grid and schedules for active month
        $this->loadCalendar();

        // 7. Load global bar chart data
        $this->loadGlobalBarChart();
    }

    public function nextVacancy()
    {
        if ($this->activeVacancies->isNotEmpty()) {
            $this->currentVacancyIndex = ($this->currentVacancyIndex + 1) % $this->activeVacancies->count();
            $this->dispatch('refresh-donut-chart', data: $this->getCurrentVacancyChartData());
        }
    }

    public function previousVacancy()
    {
        if ($this->activeVacancies->isNotEmpty()) {
            $this->currentVacancyIndex = ($this->currentVacancyIndex - 1 + $this->activeVacancies->count()) % $this->activeVacancies->count();
            $this->dispatch('refresh-donut-chart', data: $this->getCurrentVacancyChartData());
        }
    }

    public function changeMonth($direction)
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        
        if ($direction === 'next') {
            $date->addMonth();
        } else {
            $date->subMonth();
        }

        $this->currentMonth = (int) $date->format('m');
        $this->currentYear = (int) $date->format('Y');

        $this->loadCalendar();
    }

    protected function loadCalendar()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $this->monthName = $firstDay->translatedFormat('F Y');

        $schedules = InterviewSchedule::with('candidate')
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->get();

        $daysInMonth = $firstDay->daysInMonth;
        $startOfWeek = $firstDay->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        $grid = [];
        // Pad days before the first day of the month
        for ($i = 0; $i < $startOfWeek; $i++) {
            $grid[] = null;
        }

        // Fill month days
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = Carbon::create($this->currentYear, $this->currentMonth, $day)->toDateString();
            
            $daySchedules = $schedules->filter(function ($s) use ($dateString) {
                return $s->date && $s->date->toDateString() === $dateString;
            });

            $grid[] = [
                'day' => $day,
                'date' => $dateString,
                'schedules' => $daySchedules,
            ];
        }

        $this->calendarGrid = $grid;
    }

    protected function loadGlobalBarChart()
    {
        $stageCounts = \Illuminate\Support\Facades\Cache::remember('dashboard_stage_counts', 86400, function () {
            return Candidate::select('current_stage_id', \DB::raw('count(*) as count'))
                ->groupBy('current_stage_id')
                ->pluck('count', 'current_stage_id');
        });

        $barChartData = [];
        foreach ($this->stages as $stage) {
            $barChartData[] = [
                'stage' => $stage->name,
                'count' => $stageCounts->get($stage->id, 0)
            ];
        }

        $this->barChartLabels = collect($barChartData)->pluck('stage')->toArray();
        $this->barChartValues = collect($barChartData)->pluck('count')->toArray();
    }

    public function getCurrentVacancyChartData()
    {
        if ($this->activeVacancies->isEmpty()) {
            return [
                'title' => 'Tidak Ada Vacancy Aktif',
                'labels' => [],
                'values' => [],
            ];
        }

        $vacancy = $this->activeVacancies[$this->currentVacancyIndex];
        
        $stageCounts = Candidate::where('vacancy_id', $vacancy->id)
            ->select('current_stage_id', \DB::raw('count(*) as count'))
            ->groupBy('current_stage_id')
            ->pluck('count', 'current_stage_id');

        $data = [];
        foreach ($this->stages as $stage) {
            $data[] = [
                'stage' => $stage->name,
                'count' => $stageCounts->get($stage->id, 0)
            ];
        }

        return [
            'title' => $vacancy->job_title . ' (' . $vacancy->department . ')',
            'labels' => collect($data)->pluck('stage')->toArray(),
            'values' => collect($data)->pluck('count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.hr.dashboard-index');
    }
}
