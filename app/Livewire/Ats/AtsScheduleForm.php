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
    public $date;
    public $time;
    public $venue;
    public $virtual_link;

    protected function rules()
    {
        return [
            'date' => 'required|date',
            'time' => 'required',
            'venue' => 'nullable|string|max:200',
            'virtual_link' => 'nullable|url|max:200',
        ];
    }

    protected $messages = [
        'date.required' => 'Tanggal interview wajib dipilih.',
        'date.date' => 'Tanggal format salah.',
        'time.required' => 'Waktu interview wajib diisi.',
        'virtual_link.url' => 'Format tautan virtual meeting tidak valid.',
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
            $this->date = $existing->date ? $existing->date->format('Y-m-d') : null;
            $this->time = $existing->time;
            $this->venue = $existing->venue;
            $this->virtual_link = $existing->virtual_link;
        } else {
            $this->venue = $this->stage->default_location;
            $this->virtual_link = $this->stage->default_virtual_link;
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
                'date' => $this->date,
                'time' => $this->time,
                'venue' => $this->venue,
                'virtual_link' => $this->virtual_link,
            ]
        );

        session()->flash('message', 'Jadwal interview berhasil disimpan.');

        return redirect()->route('ats.candidate.detail', ['candidateId' => $this->candidateId]);
    }

    public function render()
    {
        return view('livewire.ats.schedule-form')->layout('layouts.hr');
    }
}
