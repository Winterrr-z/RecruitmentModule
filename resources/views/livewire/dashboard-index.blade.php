<div>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-surface">Halo <span class="font-extrabold text-on-surface">{{ Auth::user()->name }}</span>!</h2>
        <p class="font-body-md text-body-md text-on-surface-variant/70">Berikut adalah ringkasan metrik rekrutmen terkini.</p>
    </div>

    <!-- Main Responsive Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Widget 1: Active Vacancies -->
        <div class="bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex items-center justify-between">
            <div class="space-y-1">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Lowongan Aktif</span>
                <span class="text-3xl font-bold text-on-surface block">{{ $activeLowonganCount }}</span>
            </div>
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">work</span>
            </div>
        </div>

        <!-- Widget 2: New Candidates -->
        <div class="bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex items-center justify-between">
            <div class="space-y-1">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Kandidat Baru</span>
                <span class="text-3xl font-bold text-on-surface block">{{ $newCandidateCount }}</span>
            </div>
            <div class="w-12 h-12 bg-secondary-fixed text-on-secondary-fixed rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">person_add</span>
            </div>
        </div>

        <!-- Widget 3: Today's Interviews -->
        <div class="bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex items-center justify-between">
            <div class="space-y-1">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Interview Hari Ini</span>
                <span class="text-3xl font-bold text-on-surface block">{{ $todayInterviewCount }}</span>
            </div>
            <div class="w-12 h-12 bg-blue-500/10 text-blue-600 rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-[24px]">mic</span>
            </div>
        </div>

    </div>

    <!-- Secondary Grid: Charts & Calendars -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Widget 4: Donut Chart per Lowongan (Carousel) -->
        <div class="bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex flex-col justify-between h-[420px]">
            <div class="flex items-center justify-between pb-4 border-b border-surface-container-high/40 mb-4">
                <div>
                    <h3 class="text-title-md font-headline-lg text-on-surface">Status Lowongan</h3>
                    <p class="text-body-md text-xs text-on-surface-variant/70">Distribusi tahapan kandidat per lowongan</p>
                </div>
                
                @if (count($activeLowongans) > 1)
                    <!-- Carousel Controls -->
                    <div class="flex items-center gap-2">
                        <button wire:click="previousLowongan" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface transition-colors cursor-pointer" title="Sebelumnya">
                            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                        </button>
                        <span class="text-xs font-semibold text-on-surface-variant">{{ $currentLowonganIndex + 1 }} / {{ count($activeLowongans) }}</span>
                        <button wire:click="nextLowongan" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface transition-colors cursor-pointer" title="Selanjutnya">
                            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                        </button>
                    </div>
                @endif
            </div>

            @if (count($activeLowongans) === 0)
                <div class="flex-grow flex flex-col items-center justify-center py-12 text-center text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-[48px] mb-2">donut_large</span>
                    <p class="text-sm font-semibold">Tidak ada lowongan aktif saat ini</p>
                </div>
            @else
                @php
                    $currentJob = $activeLowongans[$currentLowonganIndex];
                @endphp
                <div class="flex-grow flex flex-col gap-4">
                    <div class="text-center">
                        <h4 class="font-bold text-primary text-base">{{ $currentJob->job_title }}</h4>
                        <p class="text-xs text-on-surface-variant/70">{{ $currentJob->department }} • Kuota: {{ $currentJob->quota }} posisi</p>
                    </div>

                    <!-- Donut Chart Container -->
                    <div class="relative h-56 flex items-center justify-center"
                         wire:ignore
                         x-data="{
                            chart: null,
                            initChart() {
                                const ctx = document.getElementById('donutChart').getContext('2d');
                                this.chart = new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: @js($this->getCurrentLowonganChartData()['labels']),
                                        datasets: [{
                                            data: @js($this->getCurrentLowonganChartData()['values']),
                                            backgroundColor: ['#6b38d4', '#fd933d', '#10b981', '#3b82f6', '#ec4899', '#f59e0b', '#8b5cf6', '#14b8a6']
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: { boxWidth: 10, font: { size: 10 } }
                                            }
                                        },
                                        cutout: '60%'
                                    }
                                });
                            },
                            updateChart(data) {
                                if (this.chart) {
                                    this.chart.data.labels = data.labels;
                                    this.chart.data.datasets[0].data = data.values;
                                    this.chart.update();
                                }
                            }
                         }"
                         x-init="initChart()"
                         @refresh-donut-chart.window="updateChart($event.detail.data)">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- Widget 5: Kalender Interview -->
        <div class="bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex flex-col justify-between h-[420px]">
            <div class="flex items-center justify-between pb-3 border-b border-surface-container-high/40 mb-3">
                <div>
                    <h3 class="text-title-md font-headline-lg text-on-surface">Kalender</h3>
                    <p class="text-body-md text-xs text-on-surface-variant/70">Jadwal pada bulan ini</p>
                </div>
                
                <!-- Month Navigator -->
                <div class="flex items-center gap-2">
                    <button wire:click="changeMonth('prev')" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface transition-colors cursor-pointer" title="Bulan Sebelumnya">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </button>
                    <span class="text-xs font-bold text-on-surface capitalize min-w-[100px] text-center">{{ $monthName }}</span>
                    <button wire:click="changeMonth('next')" class="w-8 h-8 rounded-full bg-surface-container hover:bg-surface-container-high flex items-center justify-center text-on-surface transition-colors cursor-pointer" title="Bulan Selanjutnya">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </button>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="flex-grow flex flex-col justify-between">
                <div class="grid grid-cols-7 gap-1 text-center font-bold text-[10px] uppercase tracking-wider text-on-surface-variant/75 mb-1">
                    <div>Min</div>
                    <div>Sen</div>
                    <div>Sel</div>
                    <div>Rab</div>
                    <div>Kam</div>
                    <div>Jum</div>
                    <div>Sab</div>
                </div>
                <div class="grid grid-cols-7 auto-rows-fr gap-1 flex-grow">
                    @foreach ($calendarGrid as $dateInfo)
                        @if (is_null($dateInfo))
                            <div class="bg-surface-container-low/10 border border-dashed border-surface-container/20"></div>
                        @else
                            @php
                                $isToday = $dateInfo['date'] === now()->toDateString();
                            @endphp
                            <div class="p-1 border flex flex-col justify-between transition-all duration-200 relative min-h-0
                                        {{ $isToday ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm' : 'bg-surface-container-low/30 border-surface-container/45' }}
                                        hover:bg-surface-container-high/40">
                                <span class="text-[10px] text-left">{{ $dateInfo['day'] }}</span>
                                
                                @if ($dateInfo['schedules']->isNotEmpty())
                                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative pb-0.5">
                                        <!-- Dot indicator -->
                                        <span class="w-2 h-2 bg-primary rounded-full block mx-auto cursor-pointer animate-pulse"></span>
                                        
                                        <!-- Popover list -->
                                        <div x-show="open" 
                                             x-transition
                                             style="display: none;"
                                             class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-52 bg-surface-container-lowest p-3 rounded-md shadow-lg border border-surface-container z-50 text-left pointer-events-none">
                                            <p class="text-[9px] font-bold uppercase tracking-wider text-primary mb-1.5 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[12px]">calendar_today</span>
                                                <span>Jadwal Wawancara:</span>
                                            </p>
                                            <ul class="space-y-1.5 max-h-28 overflow-y-auto">
                                                @foreach ($dateInfo['schedules'] as $sched)
                                                    <li class="text-[11px] text-on-surface border-b border-surface-container-high last:border-0 pb-1 last:pb-0">
                                                        <div class="font-bold truncate">{{ $sched->candidate->name }}</div>
                                                        <div class="text-[9px] text-on-surface-variant flex items-center gap-1 mt-0.5">
                                                            <span class="material-symbols-outlined text-[9px]">schedule</span>
                                                            <span>{{ substr($sched->time, 0, 5) }}</span>
                                                            @if ($sched->venue)
                                                                <span class="truncate">• {{ $sched->venue }}</span>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Widget 6: Bar Chart Kandidat per Stage (Global) -->
        <div class="lg:col-span-2 bg-surface-container-lowest p-6 rounded-lg shadow-sm border border-surface-container/30 flex flex-col justify-between h-[420px] mt-2">
            <div class="pb-4 border-b border-surface-container-high/40 mb-4">
                <h3 class="text-title-md font-headline-lg text-on-surface">Stage Kandidat</h3>
                <p class="text-body-md text-xs text-on-surface-variant/70">Jumlah kandidat di setiap tahapan rekrutmen</p>
            </div>

            <!-- Bar Chart Container -->
            <div class="relative h-[290px]"
                 wire:ignore
                 x-data="{
                    initChart() {
                        const ctx = document.getElementById('barChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: @js($barChartLabels),
                                datasets: [{
                                    label: 'Kandidat',
                                    data: @js($barChartValues),
                                    backgroundColor: ['#6b38d4', '#fd933d', '#10b981', '#3b82f6', '#ec4899', '#f59e0b', '#8b5cf6', '#14b8a6'],
                                    borderRadius: 4,
                                    barThickness: 90
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { 
                                            stepSize: 5,
                                            font: { size: 10 }
                                        },
                                        grid: { color: '#f3f4f6' }
                                    },
                                    x: {
                                        ticks: { font: { size: 12, weight: 'bold' } },
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    }
                 }"
                 x-init="initChart()">
                <canvas id="barChart"></canvas>
            </div>
        </div>

    </div>
</div>