<?php

namespace App\Livewire;

use Livewire\Component;

class RRDetail extends Component
{
    public $rrId;

    public function mount($id)
    {
        $this->rrId = $id;
    }

    public function render()
    {
        return '<div>Detail Recruitment Request Page Placeholder for ID: ' . e($this->rrId) . '</div>';
    }
}
