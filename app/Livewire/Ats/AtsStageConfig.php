<?php

namespace App\Livewire\Ats;

use App\Models\Stage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AtsStageConfig extends Component
{
    // List of stages
    public $stages;

    // Modal control
    public $showModal = false;
    public $isEdit = false;
    public $editingStageId = null;

    // Form inputs
    public $nama = '';
    public $deskripsi = '';
    public $butuh_scorecard = false;
    public $butuh_jadwal = false;

    // Scorecard configuration templates
    public $scorecardKriteria = [];
    
    // Scheduling configuration templates
    public $tipe_wawancara = 'online';
    public $lokasi_default = '';
    public $tautan_virtual_default = '';

    // Rule validation
    protected function rules()
    {
        return [
            'nama' => 'required|string|max:100|unique:stages,nama,' . ($this->editingStageId ?? 'NULL'),
            'deskripsi' => 'nullable|string',
            'butuh_scorecard' => 'boolean',
            'butuh_jadwal' => 'boolean',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama stage wajib diisi.',
        'nama.unique' => 'Nama stage sudah digunakan.',
        'nama.max' => 'Nama stage maksimal 100 karakter.',
    ];

    public function mount()
    {
        $this->normalizeUrutan();
        $this->loadStages();
    }

    public function loadStages()
    {
        $this->stages = Stage::orderBy('urutan', 'asc')->get();
    }

    public function addKriteria()
    {
        $this->scorecardKriteria[] = [
            'kriteria' => '',
            'bobot' => 0,
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
                ->orderBy('urutan', 'asc')
                ->get();

            // Set Applied to 1
            Stage::where('id', 1)->update(['urutan' => 1]);

            // Set middle stages starting from 2
            $current = 2;
            foreach ($middleStages as $stage) {
                $stage->update(['urutan' => $current]);
                $current++;
            }

            // Set Final to N
            Stage::where('id', 2)->update(['urutan' => $current]);
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
        $this->nama = $stage->nama;
        $this->deskripsi = $stage->deskripsi;
        $this->butuh_scorecard = (bool)$stage->butuh_scorecard;
        $this->butuh_jadwal = (bool)$stage->butuh_jadwal;

        // Load predefined criteria and scheduling configs
        $this->scorecardKriteria = $stage->scorecard_kriteria ?: [['kriteria' => '', 'bobot' => 0]];
        $this->tipe_wawancara = $stage->tipe_wawancara ?: 'online';
        $this->lokasi_default = $stage->lokasi_default ?: '';
        $this->tautan_virtual_default = $stage->tautan_virtual_default ?: '';

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // 1. Scorecard criteria validation
        if ($this->butuh_scorecard) {
            if (empty($this->scorecardKriteria)) {
                $this->addError('scorecardKriteria', 'Wajib menambahkan minimal satu kriteria penilaian.');
                return;
            }

            $totalWeight = 0;
            foreach ($this->scorecardKriteria as $index => $item) {
                if (empty(trim($item['kriteria']))) {
                    $this->addError("scorecardKriteria.{$index}.kriteria", 'Nama kriteria wajib diisi.');
                    return;
                }
                if (!isset($item['bobot']) || intval($item['bobot']) <= 0 || intval($item['bobot']) > 100) {
                    $this->addError("scorecardKriteria.{$index}.bobot", 'Bobot kriteria harus bernilai antara 1-100%.');
                    return;
                }
                $totalWeight += intval($item['bobot']);
            }

            if ($totalWeight !== 100) {
                $this->addError('totalBobot', "Total bobot kriteria harus tepat 100% (saat ini: {$totalWeight}%).");
                return;
            }
        }

        // 2. Scheduling validation
        if ($this->butuh_jadwal) {
            if (empty($this->tipe_wawancara)) {
                $this->addError('tipe_wawancara', 'Tipe wawancara wajib dipilih.');
                return;
            }
            if (($this->tipe_wawancara === 'offline' || $this->tipe_wawancara === 'hybrid') && empty(trim($this->lokasi_default))) {
                $this->addError('lokasi_default', 'Lokasi default wajib diisi untuk wawancara offline/hybrid.');
                return;
            }
            if (($this->tipe_wawancara === 'online' || $this->tipe_wawancara === 'hybrid') && !empty($this->tautan_virtual_default) && !filter_var($this->tautan_virtual_default, FILTER_VALIDATE_URL)) {
                $this->addError('tautan_virtual_default', 'Format tautan virtual default tidak valid.');
                return;
            }
        }

        $data = [
            'nama' => $this->nama,
            'deskripsi' => $this->deskripsi,
            'butuh_scorecard' => $this->butuh_scorecard,
            'butuh_jadwal' => $this->butuh_jadwal,
            'scorecard_kriteria' => $this->butuh_scorecard ? array_values($this->scorecardKriteria) : null,
            'tipe_wawancara' => $this->butuh_jadwal ? $this->tipe_wawancara : null,
            'lokasi_default' => $this->butuh_jadwal ? $this->lokasi_default : null,
            'tautan_virtual_default' => $this->butuh_jadwal ? $this->tautan_virtual_default : null,
        ];

        if ($this->isEdit) {
            $stage = Stage::findOrFail($this->editingStageId);
            $stage->update($data);
            session()->flash('message', 'Stage berhasil diperbarui.');
        } else {
            // Get current Final's order
            $finalUrutan = Stage::where('id', 2)->value('urutan') ?? 2;
            
            // Create stage with Final's current sequence, pushing it before Final
            $data['urutan'] = $finalUrutan;
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
        $prevStage = Stage::where('urutan', '<', $stage->urutan)
            ->orderBy('urutan', 'desc')
            ->first();

        if ($prevStage) {
            // Applied's position is fixed at 1
            if ($prevStage->id == 1) {
                session()->flash('error', 'Tidak dapat memindahkan stage sebelum Applied.');
                return;
            }

            DB::transaction(function () use ($stage, $prevStage) {
                $tempUrutan = $stage->urutan;
                $stage->update(['urutan' => $prevStage->urutan]);
                $prevStage->update(['urutan' => $tempUrutan]);
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
        $nextStage = Stage::where('urutan', '>', $stage->urutan)
            ->orderBy('urutan', 'asc')
            ->first();

        if ($nextStage) {
            // Final's position is fixed at the end
            if ($nextStage->id == 2) {
                session()->flash('error', 'Tidak dapat memindahkan stage setelah Final.');
                return;
            }

            DB::transaction(function () use ($stage, $nextStage) {
                $tempUrutan = $stage->urutan;
                $stage->update(['urutan' => $nextStage->urutan]);
                $nextStage->update(['urutan' => $tempUrutan]);
            });
            session()->flash('message', 'Urutan stage berhasil diperbarui.');
        }

        $this->loadStages();
    }

    public function resetForm()
    {
        $this->editingStageId = null;
        $this->nama = '';
        $this->deskripsi = '';
        $this->butuh_scorecard = false;
        $this->butuh_jadwal = false;
        $this->scorecardKriteria = [['kriteria' => '', 'bobot' => 0]];
        $this->tipe_wawancara = 'online';
        $this->lokasi_default = '';
        $this->tautan_virtual_default = '';
    }

    public function render()
    {
        $finalUrutan = Stage::where('id', 2)->value('urutan') ?? 2;
        return view('livewire.ats.stage-config', [
            'finalUrutan' => $finalUrutan,
        ])->layout('layouts.app');
    }
}
