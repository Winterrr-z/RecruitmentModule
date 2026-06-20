<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Scorecard;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class AtsCandidateDetail
 *
 * Komponen Livewire untuk menampilkan halaman detail seorang kandidat.
 * Termasuk riwayat lamaran (movement), jadwal wawancara, nilai skor (scorecard),
 * dan fitur untuk mengunduh CV atau Portofolio.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsCandidateDetail extends Component
{
    /** @var int ID kandidat yang sedang dilihat. */
    public $candidateId;

    /** @var \App\Models\Candidate|null Objek data kandidat beserta relasinya. */
    public $candidate;

    /** @var \Illuminate\Database\Eloquent\Collection Daftar riwayat perpindahan tahapan (movement). */
    public $movements = [];

    /** @var \Illuminate\Database\Eloquent\Collection Daftar jadwal wawancara di tahapan saat ini. */
    public $schedules = [];

    /** @var \Illuminate\Database\Eloquent\Collection Daftar nilai scorecard di tahapan saat ini. */
    public $scorecards = [];

    /** @var float Total nilai bobot scorecard (Σ(bobot * nilai) / 100). */
    public $totalWeightedScore = 0;

    /** @var array Menyimpan catatan (notes) wawancara sementara sebelum di-save. */
    public $notes = [];

    /** @var string URL untuk tombol kembali. */
    public $backUrl;

    /** @var string Label teks untuk tombol kembali. */
    public $backLabel;

    // Properti untuk mengontrol (expand/collapse) accordion UI
    public $expandedApplications = [];
    public $expandedScorecards = [];
    public $expandedSchedules = [];

    /**
     * Dijalankan setiap kali komponen dikembalikan (hydrated) setelah permintaan jaringan.
     * Memastikan relasi 'vacancy' dan 'currentStage' tetap termuat.
     */
    public function hydrate()
    {
        if ($this->candidate) {
            $this->candidate->load('vacancy', 'currentStage');
        }
    }

    /**
     * Inisialisasi komponen saat pertama kali dimuat.
     * Menyiapkan URL kembali, memuat data kandidat, riwayat, catatan, dan persyaratan tahapan.
     *
     * @param int $candidateId
     */
    public function mount($candidateId)
    {
        $referer = request()->headers->get('referer');
        $from = request()->query('from');

        if ($from === 'candidates' || ($referer && str_contains($referer, '/ats/candidates'))) {
            $this->backUrl = route('ats.candidates');
            $this->backLabel = 'All Candidates';
        } else {
            $this->backUrl = route('ats.dashboard');
            $this->backLabel = 'Pipeline';
        }

        $this->candidateId = $candidateId;
        $this->expandedApplications = [$candidateId];
        $this->expandedScorecards = [$candidateId];
        $this->expandedSchedules = [$candidateId];
        $this->candidate = Candidate::with('vacancy', 'currentStage')->findOrFail($candidateId);

        $this->movements = $this->candidate->candidateMovements()
            ->with('fromStage', 'toStage')
            ->orderBy('moved_at', 'desc')
            ->get();
        
        $this->loadNotes();
        
        $this->loadStageRequirements();
    }

    /**
     * Memuat dan memetakan semua catatan (notes) yang pernah ditulis HR
     * ke dalam properti array $notes berdasarkan ID movement.
     */
    public function loadNotes()
    {
        $applicationHistory = Candidate::with('candidateMovements')
            ->where('email', $this->candidate->email)
            ->get();

        foreach ($applicationHistory as $history) {
            foreach ($history->candidateMovements as $movement) {
                if (!isset($this->notes[$movement->id])) {
                    $this->notes[$movement->id] = $movement->interviewer_notes;
                }
            }
        }
    }

    /**
     * Membuka atau menutup accordion Riwayat Lamaran.
     *
     * @param int $id ID dari elemen yang di-toggle.
     */
    public function toggleApplication($id)
    {
        if (in_array($id, $this->expandedApplications)) {
            $this->expandedApplications = array_diff($this->expandedApplications, [$id]);
        } else {
            $this->expandedApplications[] = $id;
        }
    }

    /**
     * Membuka atau menutup accordion Formulir Nilai (Scorecard).
     */
    public function toggleScorecard($id)
    {
        if (in_array($id, $this->expandedScorecards)) {
            $this->expandedScorecards = array_diff($this->expandedScorecards, [$id]);
        } else {
            $this->expandedScorecards[] = $id;
        }
    }

    /**
     * Membuka atau menutup accordion Jadwal Wawancara.
     */
    public function toggleSchedule($id)
    {
        if (in_array($id, $this->expandedSchedules)) {
            $this->expandedSchedules = array_diff($this->expandedSchedules, [$id]);
        } else {
            $this->expandedSchedules[] = $id;
        }
    }

    /**
     * Menyimpan catatan (notes) pewawancara/HR ke database.
     *
     * @param int $movementId ID dari tahapan (movement) yang sedang diberi catatan.
     */
    public function saveNote($movementId)
    {
        $movement = \App\Models\CandidateMovement::findOrFail($movementId);
        $movement->update(['interviewer_notes' => $this->notes[$movementId] ?? null]);
        session()->flash('message', 'Catatan berhasil disimpan.');
        
        // Refresh active movements
        $this->movements = $this->candidate->candidateMovements()
            ->with('fromStage', 'toStage')
            ->orderBy('moved_at', 'desc')
            ->get();

        // Reload notes map
        $this->loadNotes();
    }

    /**
     * Memuat jadwal wawancara, nilai scorecard, dan menghitung total skor pembobotan
     * khusus untuk tahapan (stage) yang sedang ditempati oleh kandidat saat ini.
     */
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
            $sumWeighted = $this->scorecards->sum(fn($s) => $s->weight * $s->score);
            $this->totalWeightedScore = round($sumWeighted / 100, 2);
        } else {
            $this->totalWeightedScore = 0;
        }
    }

    /**
     * Mengunduh file CV kandidat.
     */
    public function downloadCv()
    {
        if ($this->candidate->cv_path && \Storage::disk('local')->exists($this->candidate->cv_path)) {
            $safeName = \Str::slug($this->candidate->name, '_');
            $safeJob = $this->candidate->vacancy ? \Str::slug($this->candidate->vacancy->job_title, '_') : 'mandiri';
            $ext = pathinfo($this->candidate->cv_path, PATHINFO_EXTENSION);
            $downloadName = "CV_{$safeName}_{$safeJob}.{$ext}";

            return \Storage::disk('local')->download($this->candidate->cv_path, $downloadName);
        }
        session()->flash('error', 'File CV tidak ditemukan.');
    }

    /**
     * Mengunduh file Portofolio kandidat.
     */
    public function downloadPortofolio()
    {
        if ($this->candidate->portofolio_path && \Storage::disk('local')->exists($this->candidate->portofolio_path)) {
            $safeName = \Str::slug($this->candidate->name, '_');
            $safeJob = $this->candidate->vacancy ? \Str::slug($this->candidate->vacancy->job_title, '_') : 'mandiri';
            $ext = pathinfo($this->candidate->portofolio_path, PATHINFO_EXTENSION);
            $downloadName = "Portofolio_{$safeName}_{$safeJob}.{$ext}";

            return \Storage::disk('local')->download($this->candidate->portofolio_path, $downloadName);
        }
        session()->flash('error', 'File Portofolio tidak ditemukan.');
    }

    /**
     * Render komponen antarmuka.
     * Mengambil seluruh riwayat lamaran yang pernah dilakukan oleh email kandidat ini.
     */
    public function render()
    {
        $applicationHistory = Candidate::with([
            'vacancy',
            'currentStage',
            'candidateMovements' => function($query) {
                $query->with('fromStage', 'toStage')->orderBy('moved_at', 'desc');
            },
            'interviewSchedules' => function($query) {
                $query->with('stage')->orderBy('date', 'desc')->orderBy('time', 'desc');
            },
            'scorecards' => function($query) {
                $query->with('stage');
            }
        ])
        ->where('email', $this->candidate->email)
        ->orderBy('created_at', 'desc')
        ->orderBy('id', 'desc')
        ->get();

        return view('livewire.ats.candidate-detail', [
            'applicationHistory' => $applicationHistory
        ]);
    }
}
