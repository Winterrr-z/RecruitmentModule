<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Scorecard;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class AtsScorecardForm
 *
 * Komponen Livewire untuk mengisi dan menyimpan formulir evaluasi (scorecard)
 * kandidat berdasarkan kriteria yang telah ditentukan pada tahapan (stage) tersebut.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsScorecardForm extends Component
{
    /** @var int ID Kandidat yang dievaluasi. */
    public $candidateId;

    /** @var int ID Tahapan tempat evaluasi dilakukan. */
    public $stageId;

    /** @var \App\Models\Candidate Objek kandidat terkait. */
    public $candidate;

    /** @var \App\Models\Stage Objek tahapan terkait. */
    public $stage;

    /**
     * @var array Daftar isian evaluasi.
     * Format: [['id' => null, 'criteria' => '', 'weight' => '', 'score' => '']]
     */
    public $kriteriaList = [];

    // ==========================================
    // PERHITUNGAN BOBOT & SKOR
    // ==========================================

    /** @var int Total persentase bobot (idealnya 100). */
    public $totalBobot = 0;

    /** @var float Total skor tertimbang (Σ(bobot * nilai) / 100). */
    public $totalWeightedScore = 0;

    /**
     * Memuat data awal formulir scorecard.
     * Jika scorecard sudah pernah diisi, maka nilai akan dimuat ulang.
     * Jika belum, daftar kriteria akan diisi dari format standar tahapan tersebut (template).
     *
     * @param int $candidateId
     * @param int $stageId
     */
    public function mount($candidateId, $stageId)
    {
        $this->candidateId = $candidateId;
        $this->stageId = $stageId;
        $this->candidate = Candidate::findOrFail($candidateId);
        $this->stage = Stage::findOrFail($stageId);

        // Muat nilai jika sudah pernah disimpan sebelumnya
        $existing = Scorecard::where('candidate_id', $candidateId)
            ->where('stage_id', $stageId)
            ->get();

        if ($existing->isNotEmpty()) {
            foreach ($existing as $scorecard) {
                $this->kriteriaList[] = [
                    'id' => $scorecard->id,
                    'criteria' => $scorecard->criteria,
                    'weight' => $scorecard->weight,
                    'score' => $scorecard->score,
                ];
            }
        } else {
            // Jika belum ada, gunakan template dari pengaturan stage
            $template = $this->stage->scorecard_criteria ?: [];
            foreach ($template as $item) {
                $this->kriteriaList[] = [
                    'id' => null,
                    'criteria' => $item['criteria'],
                    'weight' => $item['weight'],
                    'score' => 0, // Nilai default awal
                ];
            }
        }

        $this->calculateTotals();
    }

    /**
     * Menghitung ulang jumlah persentase bobot dan total skor tertimbang secara real-time.
     * Skor Tertimbang: Σ(bobot * nilai) / 100
     */
    public function calculateTotals()
    {
        $this->totalBobot = 0;
        $weightedSum = 0;

        foreach ($this->kriteriaList as $item) {
            $weight = (int)($item['weight'] ?? 0);
            $score = (int)($item['score'] ?? 0);

            $this->totalBobot += $weight;
            $weightedSum += ($weight * $score);
        }

        $this->totalWeightedScore = $this->totalBobot > 0 ? round($weightedSum / 100, 2) : 0;
    }

    /**
     * Menangkap perubahan data pada input nilai dan langsung memperbarui perhitungan total skor.
     *
     * @param string $propertyName Nama properti yang diubah pengguna.
     */
    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'kriteriaList')) {
            $this->calculateTotals();
        }
    }

    /**
     * Validasi data dan simpan semua kriteria evaluasi (scorecard) ke dalam database secara atomik.
     */
    public function save()
    {
        // 1. Pengecekan daftar kriteria dari konfigurasi awal
        if (empty($this->kriteriaList)) {
            $this->addError('kriteriaList', 'Tidak ada kriteria penilaian yang dikonfigurasi untuk stage ini.');
            return;
        }

        // 2. Validasi nilai input (harus berupa angka 1 - 100)
        $this->validate([
            'kriteriaList.*.score' => 'required|integer|between:1,100',
        ], [
            'kriteriaList.*.score.required' => 'Nilai wajib diisi.',
            'kriteriaList.*.score.integer' => 'Nilai harus berupa angka.',
            'kriteriaList.*.score.between' => 'Nilai harus berkisar antara 1-100.',
        ]);

        // Simpan keseluruhan menggunakan Database Transaction
        \DB::transaction(function () {
            // Hapus data nilai lama milik kandidat di tahapan ini
            Scorecard::where('candidate_id', $this->candidateId)
                ->where('stage_id', $this->stageId)
                ->delete();

            // Masukkan (insert) data evaluasi baru
            foreach ($this->kriteriaList as $item) {
                Scorecard::create([
                    'candidate_id' => $this->candidateId,
                    'stage_id' => $this->stageId,
                    'criteria' => trim($item['criteria']),
                    'weight' => (int)$item['weight'],
                    'score' => (int)$item['score'],
                ]);
            }
        });

        session()->flash('message', 'Scorecard evaluasi berhasil disimpan.');

        return redirect()->route('ats.candidate.detail', ['candidateId' => $this->candidateId]);
    }

    /**
     * Render komponen formulir scorecard.
     */
    public function render()
    {
        return view('livewire.ats.scorecard-form');
    }
}
