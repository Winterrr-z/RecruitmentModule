<?php

namespace App\Livewire\Ats;

use App\Models\Stage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AtsStageConfig extends Component
{
    // List of stages
    public $stages;

    // Modal control
    public $showModal = false;
    public $isEdit = false;
    public $editingStageId = null;

    // Form inputs
    public $name = '';
    public $description = '';
    public $needs_scorecard = false;
    public $needs_schedule = false;

    // Scorecard configuration templates
    public $scorecardKriteria = [];
    
    // Scheduling configuration templates
    public $interview_type = 'online';
    public $default_location = '';
    public $default_virtual_link = '';

    // Rule validation
    protected function rules()
    {
        return [
            'name' => 'required|string|max:100|unique:stages,nama,' . ($this->editingStageId ?? 'NULL'),
            'description' => 'nullable|string',
            'needs_scorecard' => 'boolean',
            'needs_schedule' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama stage wajib diisi.',
        'name.unique' => 'Nama stage sudah digunakan.',
        'name.max' => 'Nama stage maksimal 100 karakter.',
    ];

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
        $this->scorecardKriteria[] = [
            'criteria' => '',
            'weight' => 0,
        ];
    }

    public function removeKriteria($index)
    {
        unset($this->scorecardKriteria[$index]);
        $this->scorecardKriteria = array_values($this->scorecardKriteria);
    }

    /**
     * Normalize stage sequence numbers:
     * - Applied (id: 1) is always 1.
     * - Custom stages (middle stages) are numbered 2, 3, ... N-1.
     * - Final (id: 2) is always at the end (N).
     */
    private function normalizeUrutan()
    {
        DB::transaction(function () {
            // Get all intermediate custom stages
            $middleStages = Stage::whereNotIn('id', [1, 2])
                ->orderBy('sequence', 'asc')
                ->get();

            // Set Applied to 1
            Stage::where('id', 1)->update(['sequence' => 1]);

            // Set middle stages starting from 2
            $current = 2;
            foreach ($middleStages as $stage) {
                $stage->update(['sequence' => $current]);
                $current++;
            }

            // Set Final to N
            Stage::where('id', 2)->update(['sequence' => $current]);
        });
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function editStage($id)
    {
        $this->resetValidation();
        $stage = Stage::findOrFail($id);
        $this->editingStageId = $stage->id;
        $this->name = $stage->name;
        $this->description = $stage->description;
        $this->needs_scorecard = (bool)$stage->needs_scorecard;
        $this->needs_schedule = (bool)$stage->needs_schedule;

        // Load predefined criteria and scheduling configs
        $this->scorecardKriteria = $stage->scorecard_criteria ?: [['criteria' => '', 'weight' => 0]];
        $this->interview_type = $stage->interview_type ?: 'online';
        $this->default_location = $stage->default_location ?: '';
        $this->default_virtual_link = $stage->default_virtual_link ?: '';

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // 1. Scorecard criteria validation
        if ($this->needs_scorecard) {
            if (empty($this->scorecardKriteria)) {
                $this->addError('scorecardKriteria', 'Wajib menambahkan minimal satu kriteria penilaian.');
                return;
            }

            $totalWeight = 0;
            foreach ($this->scorecardKriteria as $index => $item) {
                if (empty(trim($item['criteria']))) {
                    $this->addError("scorecardKriteria.{$index}.kriteria", 'Nama kriteria wajib diisi.');
                    return;
                }
                if (!isset($item['weight']) || intval($item['weight']) <= 0 || intval($item['weight']) > 100) {
                    $this->addError("scorecardKriteria.{$index}.bobot", 'Bobot kriteria harus bernilai antara 1-100%.');
                    return;
                }
                $totalWeight += intval($item['weight']);
            }

            if ($totalWeight !== 100) {
                $this->addError('totalBobot', "Total bobot kriteria harus tepat 100% (saat ini: {$totalWeight}%).");
                return;
            }
        }

        // 2. Scheduling validation
        if ($this->needs_schedule) {
            if (empty($this->interview_type)) {
                $this->addError('interview_type', 'Tipe wawancara wajib dipilih.');
                return;
            }
            if (($this->interview_type === 'offline' || $this->interview_type === 'hybrid') && empty(trim($this->default_location))) {
                $this->addError('default_location', 'Lokasi default wajib diisi untuk wawancara offline/hybrid.');
                return;
            }
            if (($this->interview_type === 'online' || $this->interview_type === 'hybrid') && !empty($this->default_virtual_link) && !filter_var($this->default_virtual_link, FILTER_VALIDATE_URL)) {
                $this->addError('default_virtual_link', 'Format tautan virtual default tidak valid.');
                return;
            }
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'needs_scorecard' => $this->needs_scorecard,
            'needs_schedule' => $this->needs_schedule,
            'scorecard_criteria' => $this->needs_scorecard ? array_values($this->scorecardKriteria) : null,
            'interview_type' => $this->needs_schedule ? $this->interview_type : null,
            'default_location' => $this->needs_schedule ? $this->default_location : null,
            'default_virtual_link' => $this->needs_schedule ? $this->default_virtual_link : null,
        ];

        if ($this->isEdit) {
            $stage = Stage::findOrFail($this->editingStageId);
            $stage->update($data);
            session()->flash('message', 'Stage berhasil diperbarui.');
        } else {
            // Get current Final's order
            $finalUrutan = Stage::where('id', 2)->value('sequence') ?? 2;
            
            // Create stage with Final's current sequence, pushing it before Final
            $data['sequence'] = $finalUrutan;
            Stage::create($data);
            session()->flash('message', 'Stage baru berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->normalizeUrutan();
        $this->loadStages();
    }

    public function deleteStage($id)
    {
        if (in_array($id, [1, 2])) {
            session()->flash('error', 'Stage Applied dan Final tidak dapat dihapus.');
            return;
        }

        $stage = Stage::findOrFail($id);

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
        if (in_array($id, [1, 2])) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }

        $stage = Stage::findOrFail($id);
        
        // Find stage with highest urutan that is smaller than current
        $prevStage = Stage::where('sequence', '<', $stage->sequence)
            ->orderBy('sequence', 'desc')
            ->first();

        if ($prevStage) {
            // Applied's position is fixed at 1
            if ($prevStage->id == 1) {
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
        if (in_array($id, [1, 2])) {
            session()->flash('error', 'Urutan stage Applied dan Final tidak dapat diubah.');
            return;
        }

        $stage = Stage::findOrFail($id);
        
        // Find stage with lowest urutan that is larger than current
        $nextStage = Stage::where('sequence', '>', $stage->sequence)
            ->orderBy('sequence', 'asc')
            ->first();

        if ($nextStage) {
            // Final's position is fixed at the end
            if ($nextStage->id == 2) {
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

    public function resetForm()
    {
        $this->editingStageId = null;
        $this->name = '';
        $this->description = '';
        $this->needs_scorecard = false;
        $this->needs_schedule = false;
        $this->scorecardKriteria = [['criteria' => '', 'weight' => 0]];
        $this->interview_type = 'online';
        $this->default_location = '';
        $this->default_virtual_link = '';
    }

    public function render()
    {
        $finalUrutan = Stage::where('id', 2)->value('sequence') ?? 2;
        return view('livewire.ats.stage-config', [
            'finalUrutan' => $finalUrutan,
        ]);
    }
}
