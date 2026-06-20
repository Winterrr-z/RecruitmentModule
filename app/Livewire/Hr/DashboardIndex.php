<?php

namespace App\Livewire\Hr;

use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Stage;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class DashboardIndex
 *
 * Komponen Livewire untuk menampilkan halaman utama Dashboard HR.
 * Komponen ini menangani perhitungan metrik utama, grafik Donut (berdasarkan lowongan),
 * grafik Bar (global), serta kalender jadwal wawancara bulanan.
 *
 * @package App\Livewire\Hr
 */
#[Layout('layouts.hr')]
class DashboardIndex extends Component
{
    // ==========================================
    // METRIK UTAMA (KARTU ATAS)
    // ==========================================
    
    /** @var int Jumlah lowongan (vacancy) aktif yang belum melewati batas waktu (deadline) dan kuotanya belum penuh. */
    public int $activeVacancyCount = 0;

    /** @var int Jumlah kandidat pelamar baru yang masih berada di tahapan (stage) paling awal dan belum pernah dipindah. */
    public int $newCandidateCount = 0;

    /** @var int Jumlah jadwal wawancara (interview) yang pelaksanaannya jatuh pada hari ini. */
    public int $todayInterviewCount = 0;

    // ==========================================
    // GRAFIK DONUT (BERDASARKAN LOWONGAN)
    // ==========================================

    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Vacancy[] Daftar lowongan aktif yang bisa digeser di grafik Donut. */
    public $activeVacancies;

    /** @var int Indeks array dari lowongan yang sedang ditampilkan grafiknya saat ini. */
    public int $currentVacancyIndex = 0;

    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Stage[] Daftar seluruh tahapan (stage) lamaran yang ada di sistem (di-cache untuk performa). */
    public $stages;

    // ==========================================
    // KALENDER JADWAL WAWANCARA
    // ==========================================

    /** @var int Bulan kalender yang sedang aktif/dilihat (1-12). */
    public int $currentMonth;

    /** @var int Tahun kalender yang sedang aktif/dilihat (contoh: 2026). */
    public int $currentYear;

    /** @var string Nama bulan dan tahun yang diformat untuk judul kalender (contoh: "Juni 2026"). */
    public string $monthName = '';

    /** @var array Grid matriks kalender yang berisi data tanggal kosong dan tanggal yang memiliki jadwal. */
    public array $calendarGrid = [];

    // ==========================================
    // GRAFIK BAR GLOBAL (SEMUA KANDIDAT)
    // ==========================================

    /** @var array Daftar nama tahapan (stage) untuk sumbu X pada grafik Bar. */
    public array $barChartLabels = [];

    /** @var array Daftar jumlah kandidat per tahapan untuk sumbu Y pada grafik Bar. */
    public array $barChartValues = [];

    /**
     * Dijalankan pertama kali saat komponen dimuat (Life-cycle hook).
     * Mengatur bahasa aplikasi ke Indonesia dan mengatur kalender ke bulan & tahun saat ini.
     */
    public function mount()
    {
        Carbon::setLocale('id'); // Ubah format tanggal menjadi bahasa Indonesia
        
        $this->currentMonth = (int) now()->format('m');
        $this->currentYear  = (int) now()->format('Y');

        $this->loadData();
    }

    /**
     * Mengambil dan memproses seluruh data utama untuk dashboard dari database.
     */
    public function loadData()
    {
        // 1. Menghitung jumlah lowongan aktif (status Published, kuota tersedia, dan belum melewati deadline)
        $this->activeVacancyCount = Vacancy::where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->count();

        // 2. Memuat seluruh data tahapan (Stage) dari cache database
        $this->stages = Stage::getAllCached();

        // 3. Menghitung jumlah kandidat baru
        // Berada di stage urutan pertama (is_first_stage) dan belum memiliki riwayat perpindahan (movements)
        $firstStageId = $this->stages->where('is_first_stage', true)->first()?->id ?? 1;
        $this->newCandidateCount = Candidate::where('current_stage_id', $firstStageId)
            ->whereDoesntHave('movements')
            ->count();

        // 4. Menghitung jumlah jadwal wawancara untuk hari ini
        $this->todayInterviewCount = InterviewSchedule::whereDate('date', today())->count();

        // 5. Mengambil detail lowongan aktif untuk fitur geser di Grafik Donut
        $this->activeVacancies = Vacancy::where('status', 'Published')
            ->where('quota', '>', 0)
            ->where('application_deadline', '>=', now()->toDateString())
            ->get();

        // 6. Membangun struktur grid kalender (kotak-kotak tanggal) beserta jadwalnya
        $this->loadCalendar();

        // 7. Menghitung data sebaran kandidat global untuk Grafik Bar
        $this->loadGlobalBarChart();
    }

    /**
     * Memutar grafik Donut ke data lowongan berikutnya.
     * Mengirimkan event (dispatch) ke Javascript untuk me-render ulang grafiknya.
     */
    public function nextVacancy()
    {
        if ($this->activeVacancies->isNotEmpty()) {
            $this->currentVacancyIndex = ($this->currentVacancyIndex + 1) % $this->activeVacancies->count();
            $this->dispatch('refresh-donut-chart', data: $this->getCurrentVacancyChartData());
        }
    }

    /**
     * Memutar grafik Donut ke data lowongan sebelumnya.
     * Mengirimkan event (dispatch) ke Javascript untuk me-render ulang grafiknya.
     */
    public function previousVacancy()
    {
        if ($this->activeVacancies->isNotEmpty()) {
            $this->currentVacancyIndex = ($this->currentVacancyIndex - 1 + $this->activeVacancies->count()) % $this->activeVacancies->count();
            $this->dispatch('refresh-donut-chart', data: $this->getCurrentVacancyChartData());
        }
    }

    /**
     * Mengganti bulan aktif pada kalender.
     *
     * @param string $direction Arah pergeseran ('next' untuk bulan depan, 'prev' untuk bulan lalu).
     */
    public function changeMonth($direction)
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1);
        
        if ($direction === 'next') {
            $date->addMonth();
        } else {
            $date->subMonth();
        }

        $this->currentMonth = (int) $date->format('m');
        $this->currentYear  = (int) $date->format('Y');

        $this->loadCalendar(); // Muat ulang jadwal untuk bulan yang baru
    }

    /**
     * Membangun struktur grid kalender beserta data jadwal wawancaranya.
     */
    protected function loadCalendar()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $this->monthName = $firstDay->translatedFormat('F Y');

        // Ambil semua jadwal interview di bulan yang sedang aktif
        $schedules = InterviewSchedule::with('candidate')
            ->whereMonth('date', $this->currentMonth)
            ->whereYear('date', $this->currentYear)
            ->get();

        $daysInMonth = $firstDay->daysInMonth;
        $startOfWeek = $firstDay->dayOfWeek; // 0 = Minggu, 1 = Senin, dst.

        $grid = [];
        // Tambahkan kotak kosong (null) di awal grid untuk menyamakan posisi hari pertama
        for ($i = 0; $i < $startOfWeek; $i++) {
            $grid[] = null;
        }

        // Isi kotak-kotak penanggalan
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = Carbon::create($this->currentYear, $this->currentMonth, $day)->toDateString();
            
            // Saring jadwal yang khusus jatuh pada tanggal ini
            $daySchedules = $schedules->filter(function ($s) use ($dateString) {
                return $s->date && $s->date->toDateString() === $dateString;
            });

            $grid[] = [
                'day'       => $day,
                'date'      => $dateString,
                'schedules' => $daySchedules,
            ];
        }

        $this->calendarGrid = $grid;
    }

    /**
     * Mengkalkulasi data untuk Grafik Bar (Sebaran Kandidat secara Global).
     * Disimpan di dalam Cache (1 hari) untuk menghemat beban database.
     */
    protected function loadGlobalBarChart()
    {
        $stageCounts = \Illuminate\Support\Facades\Cache::remember('dashboard_stage_counts', 86400, function () {
            // Mengelompokkan dan menghitung jumlah kandidat berdasarkan ID tahapan
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

        // Pisahkan menjadi 2 array untuk sumbu X (Label) dan sumbu Y (Values)
        $this->barChartLabels = collect($barChartData)->pluck('stage')->toArray();
        $this->barChartValues = collect($barChartData)->pluck('count')->toArray();
    }

    /**
     * Mengambil data grafik Donut khusus untuk 1 lowongan yang sedang dilihat di Carousel.
     *
     * @return array Data terformat untuk dikirim ke Javascript (title, labels, values)
     */
    public function getCurrentVacancyChartData()
    {
        // Jika tidak ada lowongan yang aktif, kembalikan data kosong
        if ($this->activeVacancies->isEmpty()) {
            return [
                'title'  => 'Tidak Ada Vacancy Aktif',
                'labels' => [],
                'values' => [],
            ];
        }

        $vacancy = $this->activeVacancies[$this->currentVacancyIndex];
        
        // Menghitung sebaran kandidat (per tahapan) HANYA untuk lowongan ini
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
            'title'  => $vacancy->job_title . ' (' . $vacancy->department . ')',
            'labels' => collect($data)->pluck('stage')->toArray(),
            'values' => collect($data)->pluck('count')->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.hr.dashboard-index');
    }
}
