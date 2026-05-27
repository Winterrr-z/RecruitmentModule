<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\InterviewSchedule;
use Livewire\Component;

class AtsScheduleForm extends Component
{
    public $candidateId;
    public $stageId;
    public $candidate;
    public $stage;

    // Form fields
    public $tanggal;
    public $waktu;
    public $tempat;
    public $tautan_virtual;

    protected function rules()
    {
        return [
            'tanggal' => 'required|date',
            'waktu' => 'required',
            'tempat' => 'nullable|string|max:200',
            'tautan_virtual' => 'nullable|url|max:200',
        ];
    }

    protected $messages = [
        'tanggal.required' => 'Tanggal interview wajib dipilih.',
        'tanggal.date' => 'Tanggal format salah.',
        'waktu.required' => 'Waktu interview wajib diisi.',
        'tautan_virtual.url' => 'Format tautan virtual meeting tidak valid.',
    ];

    public function mount($candidateId, $stageId)
    {
        $this->candidateId = $candidateId;
        $this->stageId = $stageId;
        $this->candidate = Candidate::findOrFail($candidateId);
        $this->stage = Stage::findOrFail($stageId);

        // Load existing schedule if exists
        $existing = InterviewSchedule::where('candidate_id', $candidateId)
            ->where('stage_id', $stageId)
            ->first();

        if ($existing) {
            $this->tanggal = $existing->tanggal ? $existing->tanggal->format('Y-m-d') : null;
            $this->waktu = $existing->waktu;
            $this->tempat = $existing->tempat;
            $this->tautan_virtual = $existing->tautan_virtual;
        } else {
            $this->tempat = $this->stage->lokasi_default;
            $this->tautan_virtual = $this->stage->tautan_virtual_default;
        }
    }

    public function save()
    {
        $this->validate();

        InterviewSchedule::updateOrCreate(
            [
                'candidate_id' => $this->candidateId,
                'stage_id' => $this->stageId,
            ],
            [
                'tanggal' => $this->tanggal,
                'waktu' => $this->waktu,
                'tempat' => $this->tempat,
                'tautan_virtual' => $this->tautan_virtual,
            ]
        );

        session()->flash('message', 'Jadwal interview berhasil disimpan.');

        return redirect()->route('ats.candidate.detail', ['candidateId' => $this->candidateId]);
    }

    public function render()
    {
        return view('livewire.ats.schedule-form')->layout('layouts.app');
    }
}
