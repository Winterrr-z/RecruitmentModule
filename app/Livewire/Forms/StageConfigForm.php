<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Stage;

class StageConfigForm extends Form
{
    public ?Stage $stage = null;

    public $name = '';
    public $description = '';
    public $needs_scorecard = false;
    public $needs_schedule = false;
    public $scorecardKriteria = [];
    public $interview_type = 'online';
    public $default_location = '';
    public $default_virtual_link = '';

    public function setStage(Stage $stage)
    {
        $this->stage = $stage;
        $this->name = $stage->name;
        $this->description = $stage->description;
        $this->needs_scorecard = (bool)$stage->needs_scorecard;
        $this->needs_schedule = (bool)$stage->needs_schedule;
        $this->scorecardKriteria = $stage->scorecard_criteria ?: [['criteria' => '', 'weight' => 0]];
        $this->interview_type = $stage->interview_type ?: 'online';
        $this->default_location = $stage->default_location ?: '';
        $this->default_virtual_link = $stage->default_virtual_link ?: '';
    }

    public function rules()
    {
        $stageId = $this->stage ? $this->stage->id : 'NULL';
        return [
            'name' => 'required|string|max:100|unique:stages,name,' . $stageId,
            'description' => 'nullable|string',
            'needs_scorecard' => 'boolean',
            'needs_schedule' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama stage wajib diisi.',
            'name.unique' => 'Nama stage sudah digunakan.',
            'name.max' => 'Nama stage maksimal 100 karakter.',
        ];
    }

    public function resetForm()
    {
        $this->stage = null;
        $this->name = '';
        $this->description = '';
        $this->needs_scorecard = false;
        $this->needs_schedule = false;
        $this->scorecardKriteria = [['criteria' => '', 'weight' => 0]];
        $this->interview_type = 'online';
        $this->default_location = '';
        $this->default_virtual_link = '';
    }
}
