<?php

namespace App\Livewire\Ats;

use App\Models\Stage;
use App\Livewire\Forms\StageConfigForm;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
class AtsStageConfig extends Component
{
    public StageConfigForm $form;

    // List of stages
    public $stages;

    // Modal control
    public $showModal = false;
    public $isEdit = false;

    public function mount()
    {
        $this->normalizeUrutan();
        $this->loadStages();
    }

    public function loadStages()
    {
        $this->stages = Stage::getAllCached();
    }

    public function addKriteria()
    {
        $this->form->scorecardKriteria[] = [
            'criteria' => '',
            'weight' => 0,
        ];
    }

    public function removeKriteria($index)
    {
        unset($this->form->scorecardKriteria[$index]);
        $this->form->scorecardKriteria = array_values($this->form->scorecardKriteria);
    }

    /**
     * Normalize stage sequence numbers:
     * - Applied (is_first_stage) is always 1.
     * - Custom stages (middle stages) are numbered 2, 3, ... N-1.
     * - Final (is_final_stage) is always at the end (N).
     */
    private function normalizeUrutan()
    {
        DB::transaction(function () {
            // Get all intermediate custom stages
            $middleStages = Stage::where('is_first_stage', false)
                ->where('is_final_stage', false)
                ->orderBy('sequence', 'asc')
                ->get();

            // Set Applied to 1
            Stage::where('is_first_stage', true)->update(['sequence' => 1]);

            // Set middle stages starting from 2
            $current = 2;
            foreach ($middleStages as $stage) {
                $stage->update(['sequence' => $current]);
                $current++;
            }

            // Set Final to N
            Stage::where('is_final_stage', true)->update(['sequence' => $current]);
        });
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->form->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function editStage($id)
    {
        $this->resetValidation();
        $stage = Stage::findOrFail($id);
        $this->form->setStage($stage);

        $this->isEdit = true;
        $this->showModal = true;
    }

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
                    $this->addError("form.scorecardKriteria.{$index}.kriteria", 'Nama kriteria wajib diisi.');
                    return;
                }
                if (!isset($item['weight']) || intval($item['weight']) <= 0 || intval($item['weight']) > 100) {
                    $this->addError("form.scorecardKriteria.{$index}.bobot", 'Bobot kriteria harus bernilai antara 1-100%.');
                    return;
                }
                $totalWeight += intval($item['weight']);
            }

            if ($totalWeight !== 100) {
                $this->addError('form.totalBobot', "Total bobot kriteria harus tepat 100% (saat ini: {$totalWeight}%).");
                return;
            }
        }

        // 2. Scheduling validation
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
            $stage->update($data);
            session()->flash('message', 'Stage berhasil diperbarui.');
        } else {
            // Get current Final's order
            $finalUrutan = Stage::where('is_final_stage', true)->value('sequence') ?? 2;
            
            // Create stage with Final's current sequence, pushing it before Final
            $data['sequence'] = $finalUrutan;
            Stage::create($data);
            session()->flash('message', 'Stage baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->form->resetForm();
        $this->normalizeUrutan();
        $this->loadStages();
    }

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

    public function moveUp($id)
    {
        $stage = Stage::findOrFail($id);

        if ($stage->is_first_stage || $stage->is_final_stage) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }
        
        // Find stage with highest urutan that is smaller than current
        $prevStage = Stage::where('sequence', '<', $stage->sequence)
            ->orderBy('sequence', 'desc')
            ->first();

        if ($prevStage) {
            // Applied's position is fixed at 1
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

    public function moveDown($id)
    {
        $stage = Stage::findOrFail($id);

        if ($stage->is_first_stage || $stage->is_final_stage) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }
        
        // Find stage with lowest urutan that is larger than current
        $nextStage = Stage::where('sequence', '>', $stage->sequence)
            ->orderBy('sequence', 'asc')
            ->first();

        if ($nextStage) {
            // Final's position is fixed at the end
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

    public function render()
    {
        $finalUrutan = Stage::where('is_final_stage', true)->value('sequence') ?? 2;
        return view('livewire.ats.stage-config', [
            'finalUrutan' => $finalUrutan,
        ]);
    }
}
