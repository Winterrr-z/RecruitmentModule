<?php

namespace App\Livewire;

use App\Models\Lowongan;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Stage;
use Carbon\Carbon;
use Livewire\Component;

class DashboardIndex extends Component
{
    // Metric counts
    public $activeLowonganCount = 0;
    public $newCandidateCount = 0;
    public $todayInterviewCount = 0;

    // Donut Carousel
    public $activeLowongans = [];
    public $currentLowonganIndex = 0;
    public $stages = [];

    // Calendar
    public $currentMonth;
    public $currentYear;
    public $monthName;
    public $calendarGrid = [];

    // Global Bar Chart
    public $barChartLabels = [];
    public $barChartValues = [];

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
        $this->activeLowonganCount = Lowongan::where('status', 'Published')
            ->where('kuota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->count();

        // 2. Widget 2: New candidate count (Applied stage, no movements)
        $this->newCandidateCount = Candidate::where('current_stage_id', 1)
            ->whereDoesntHave('movements')
            ->count();

        // 3. Widget 3: Today's interviews count
        $this->todayInterviewCount = InterviewSchedule::whereDate('tanggal', today())->count();

        // 4. Load stages
        $this->stages = Stage::orderBy('urutan', 'asc')->get();

        // 5. Load active vacancies for Donut Carousel
        $this->activeLowongans = Lowongan::where('status', 'Published')
            ->where('kuota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->get();

        // 6. Load calendar grid and schedules for active month
        $this->loadCalendar();

        // 7. Load global bar chart data
        $this->loadGlobalBarChart();
    }

    public function nextLowongan()
    {
        if ($this->activeLowongans->isNotEmpty()) {
            $this->currentLowonganIndex = ($this->currentLowonganIndex + 1) % $this->activeLowongans->count();
            $this->dispatch('refreshDonutChart', data: $this->getCurrentLowonganChartData());
        }
    }

    public function previousLowongan()
    {
        if ($this->activeLowongans->isNotEmpty()) {
            $this->currentLowonganIndex = ($this->currentLowonganIndex - 1 + $this->activeLowongans->count()) % $this->activeLowongans->count();
            $this->dispatch('refreshDonutChart', data: $this->getCurrentLowonganChartData());
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
            ->whereMonth('tanggal', $this->currentMonth)
            ->whereYear('tanggal', $this->currentYear)
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
                return $s->tanggal && $s->tanggal->toDateString() === $dateString;
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
        $barChartData = [];
        foreach ($this->stages as $stage) {
            $count = Candidate::where('current_stage_id', $stage->id)->count();
            $barChartData[] = [
                'stage' => $stage->nama,
                'count' => $count
            ];
        }

        $this->barChartLabels = collect($barChartData)->pluck('stage')->toArray();
        $this->barChartValues = collect($barChartData)->pluck('count')->toArray();
    }

    public function getCurrentLowonganChartData()
    {
        if ($this->activeLowongans->isEmpty()) {
            return [
                'title' => 'Tidak Ada Lowongan Aktif',
                'labels' => [],
                'values' => [],
            ];
        }

        $lowongan = $this->activeLowongans[$this->currentLowonganIndex];
        
        $data = [];
        foreach ($this->stages as $stage) {
            $count = Candidate::where('lowongan_id', $lowongan->id)
                ->where('current_stage_id', $stage->id)
                ->count();
            
            $data[] = [
                'stage' => $stage->nama,
                'count' => $count
            ];
        }

        return [
            'title' => $lowongan->jabatan . ' (' . $lowongan->departemen . ')',
            'labels' => collect($data)->pluck('stage')->toArray(),
            'values' => collect($data)->pluck('count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-index')->layout('layouts.app');
    }
}
