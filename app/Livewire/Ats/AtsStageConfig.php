<?php

namespace App\Livewire\Ats;

use App\Models\Stage;
use App\Livewire\Ats\StageConfigForm;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class AtsStageConfig
 *
 * Komponen Livewire untuk mengatur konfigurasi tahapan rekrutmen (Pipeline Stages).
 * Termasuk menambah, mengubah urutan, serta menentukan syarat scorecard dan wawancara 
 * di setiap tahapannya.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsStageConfig extends Component
{
    /** @var \App\Livewire\Ats\StageConfigForm Objek penampung nilai form (Livewire Form Object). */
    public StageConfigForm $form;

    /** @var \Illuminate\Database\Eloquent\Collection Daftar seluruh tahapan. */
    public $stages;

    // ==========================================
    // PROPERTI MODAL
    // ==========================================

    /** @var bool Status tampil/sembunyikan modal pengaturan tahapan. */
    public $showModal = false;

    /** @var bool Menandakan apakah modal dibuka untuk pengubahan (edit) atau penambahan (tambah baru). */
    public $isEdit = false;

    /** @var bool Menandakan jika konfigurasi scorecard terkunci karena sudah ada nilai kandidat (tidak boleh diedit). */
    public $isScorecardLocked = false;

    /**
     * Inisialisasi awal. Memperbaiki urutan dan memuat semua tahapan.
     */
    public function mount()
    {
        $this->normalizeUrutan();
        $this->loadStages();
    }

    /**
     * Memuat ulang daftar tahapan dari database/cache.
     */
    public function loadStages()
    {
        $this->stages = Stage::getAllCached();
    }

    /**
     * Menambahkan baris kosong ke dalam formulir pembuatan kriteria scorecard.
     */
    public function addKriteria()
    {
        $this->form->scorecardKriteria[] = [
            'criteria' => '',
            'weight' => 0,
        ];
    }

    /**
     * Menghapus salah satu kriteria scorecard berdasarkan urutannya di form.
     *
     * @param int $index
     */
    public function removeKriteria($index)
    {
        unset($this->form->scorecardKriteria[$index]);
        $this->form->scorecardKriteria = array_values($this->form->scorecardKriteria);
    }

    /**
     * Merapikan nomor urut tahapan (sequence number):
     * - 'Applied' (is_first_stage) selalu berada di urutan 1.
     * - 'Final' (is_final_stage) selalu berada di urutan terakhir.
     * - Tahapan lain diurutkan mulai dari angka 2.
     */
    public function normalizeUrutan()
    {
        $stages = Stage::orderBy('sequence', 'asc')->get();
        
        DB::transaction(function () use ($stages) {
            $applied = $stages->where('is_first_stage', true)->first();
            $final = $stages->where('is_final_stage', true)->first();
            $customStages = $stages->where('is_first_stage', false)->where('is_final_stage', false)->values();

            if ($applied) {
                $applied->update(['sequence' => 1]);
            }

            $currentUrutan = 2;
            foreach ($customStages as $stage) {
                $stage->update(['sequence' => $currentUrutan]);
                $currentUrutan++;
            }

            if ($final) {
                $final->update(['sequence' => $currentUrutan]);
            }
        });
    }

    /**
     * Membuka modal untuk menambah tahapan baru (reset isi form).
     */
    public function openAddModal()
    {
        $this->resetValidation();
        $this->form->resetForm();
        $this->isEdit = false;
        $this->isScorecardLocked = false;
        $this->showModal = true;
    }

    /**
     * Membuka modal untuk mengubah tahapan lama.
     * Mengecek apakah tahapan ini memiliki scorecard yang terkunci.
     *
     * @param int $id ID tahapan.
     */
    public function editStage($id)
    {
        $this->resetValidation();
        $stage = Stage::findOrFail($id);
        $this->form->setStage($stage);
        $this->isScorecardLocked = \App\Models\Scorecard::where('stage_id', $id)->exists();

        $this->isEdit = true;
        $this->showModal = true;
    }

    /**
     * Menyimpan data tahapan baru atau perubahan tahapan lama ke database.
     * Melakukan berbagai pemeriksaan keabsahan data scorecard dan wawancara.
     */
    public function save()
    {
        $this->form->validate();

        // 1. Scorecard criteria validation
        if ($this->form->needs_scorecard) {
            if (empty($this->form->scorecardKriteria)) {
                $this->addError('form.scorecardKriteria', 'Wajib menambahkan minimal satu kriteria penilaian.');
                return;
            }

            $totalWeight = 0;
            foreach ($this->form->scorecardKriteria as $index => $item) {
                if (empty(trim($item['criteria'] ?? ''))) {
                    $this->addError("form.scorecardKriteria.{$index}.criteria", 'Nama kriteria wajib diisi.');
                    return;
                }
                if (!isset($item['weight']) || intval($item['weight']) <= 0 || intval($item['weight']) > 100) {
                    $this->addError("form.scorecardKriteria.{$index}.weight", 'Bobot kriteria harus bernilai antara 1-100%.');
                    return;
                }
                $totalWeight += intval($item['weight']);
            }

            if ($totalWeight !== 100) {
                $this->addError('form.totalBobot', "Total bobot kriteria harus tepat 100% (saat ini: {$totalWeight}%).");
                return;
            }
        }

        // 2. Validasi pengaturan jadwal wawancara (Scheduling validation)
        if ($this->form->needs_schedule) {
            if (empty($this->form->interview_type)) {
                $this->addError('form.interview_type', 'Tipe wawancara wajib dipilih.');
                return;
            }
            if (($this->form->interview_type === 'offline' || $this->form->interview_type === 'hybrid') && empty(trim($this->form->default_location))) {
                $this->addError('form.default_location', 'Lokasi default wajib diisi untuk wawancara offline/hybrid.');
                return;
            }
            if (($this->form->interview_type === 'online' || $this->form->interview_type === 'hybrid') && !empty($this->form->default_virtual_link) && !filter_var($this->form->default_virtual_link, FILTER_VALIDATE_URL)) {
                $this->addError('form.default_virtual_link', 'Format tautan virtual default tidak valid.');
                return;
            }
        }

        $data = [
            'name' => $this->form->name,
            'description' => $this->form->description,
            'needs_scorecard' => $this->form->needs_scorecard,
            'needs_schedule' => $this->form->needs_schedule,
            'scorecard_criteria' => $this->form->needs_scorecard ? array_values($this->form->scorecardKriteria) : null,
            'interview_type' => $this->form->needs_schedule ? $this->form->interview_type : null,
            'default_location' => $this->form->needs_schedule ? $this->form->default_location : null,
            'default_virtual_link' => $this->form->needs_schedule ? $this->form->default_virtual_link : null,
        ];

        if ($this->isEdit) {
            $stage = Stage::findOrFail($this->form->stage->id);

            // Pengecekan keamanan sisi server: mencegah modifikasi scorecard jika sudah ada yang ternilai
            $hasScorecardEvaluations = \App\Models\Scorecard::where('stage_id', $stage->id)->exists();
            if ($hasScorecardEvaluations) {
                $oldNeeds = (bool)$stage->needs_scorecard;
                $newNeeds = (bool)$this->form->needs_scorecard;
                
                $oldCriteria = json_encode($stage->scorecard_criteria);
                $newCriteria = json_encode($this->form->needs_scorecard ? array_values($this->form->scorecardKriteria) : null);
                
                if ($oldNeeds !== $newNeeds || $oldCriteria !== $newCriteria) {
                    $this->addError('form.scorecardKriteria', 'Tidak bisa merubah scorecard karena sudah ada kandidat yang dinilai.');
                    return;
                }
            }

            $stage->update($data);
            session()->flash('message', 'Stage berhasil diperbarui.');
        } else {
            // Ambil nomor urut tahap 'Final' saat ini
            $finalUrutan = Stage::where('is_final_stage', true)->value('sequence') ?? 2;
            
            // Simpan tahapan baru tepat sebelum tahap 'Final'
            $data['sequence'] = $finalUrutan;
            Stage::create($data);
            session()->flash('message', 'Stage baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->form->resetForm();
        $this->normalizeUrutan();
        $this->loadStages();
    }

    /**
     * Menghapus tahapan.
     * Akan ditolak jika tahapan merupakan 'Applied' atau 'Final', atau masih berisi kandidat.
     *
     * @param int $id ID tahapan.
     */
    public function deleteStage($id)
    {
        $stage = Stage::findOrFail($id);

        if ($stage->is_first_stage || $stage->is_final_stage) {
            session()->flash('error', 'Stage Applied dan Final tidak dapat dihapus.');
            return;
        }

        if ($stage->candidates()->count() > 0) {
            session()->flash('error', 'Stage ini tidak dapat dihapus karena masih memiliki kandidat aktif.');
            return;
        }

        $stage->delete();
        session()->flash('message', 'Stage berhasil dihapus.');
        
        $this->normalizeUrutan();
        $this->loadStages();
    }

    /**
     * Menaikkan urutan tahapan ke atas.
     * Tahapan pertama ('Applied') tidak bisa diganggu gugat.
     *
     * @param int $id ID tahapan.
     */
    public function moveUp($id)
    {
        $stage = Stage::findOrFail($id);

        if ($stage->is_first_stage || $stage->is_final_stage) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }
        
        // Mencari tahapan yang berada tepat di atas tahapan ini
        $prevStage = Stage::where('sequence', '<', $stage->sequence)
            ->orderBy('sequence', 'desc')
            ->first();

        if ($prevStage) {
            // Posisi 'Applied' (1) selalu menetap
            if ($prevStage->is_first_stage) {
                session()->flash('error', 'Tidak dapat memindahkan stage sebelum Applied.');
                return;
            }

            DB::transaction(function () use ($stage, $prevStage) {
                $tempUrutan = $stage->sequence;
                $stage->update(['sequence' => $prevStage->sequence]);
                $prevStage->update(['sequence' => $tempUrutan]);
            });
            session()->flash('message', 'Urutan stage berhasil diperbarui.');
        }

        $this->loadStages();
    }

    /**
     * Menurunkan urutan tahapan ke bawah.
     * Tahapan terakhir ('Final') tidak bisa diganggu gugat.
     *
     * @param int $id ID tahapan.
     */
    public function moveDown($id)
    {
        $stage = Stage::findOrFail($id);

        if ($stage->is_first_stage || $stage->is_final_stage) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }
        
        // Mencari tahapan yang berada tepat di bawah tahapan ini
        $nextStage = Stage::where('sequence', '>', $stage->sequence)
            ->orderBy('sequence', 'asc')
            ->first();

        if ($nextStage) {
            // Posisi 'Final' selalu menetap di akhir
            if ($nextStage->is_final_stage) {
                session()->flash('error', 'Tidak dapat memindahkan stage setelah Final.');
                return;
            }

            DB::transaction(function () use ($stage, $nextStage) {
                $tempUrutan = $stage->sequence;
                $stage->update(['sequence' => $nextStage->sequence]);
                $nextStage->update(['sequence' => $tempUrutan]);
            });
            session()->flash('message', 'Urutan stage berhasil diperbarui.');
        }

        $this->loadStages();
    }

    /**
     * Render komponen antarmuka konfigurasi tahapan.
     */
    public function render()
    {
        $finalUrutan = Stage::where('is_final_stage', true)->value('sequence') ?? 2;
        return view('livewire.ats.stage-config', [
            'finalUrutan' => $finalUrutan,
        ]);
    }
}
