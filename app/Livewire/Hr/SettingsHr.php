<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
class SettingsHr extends Component
{
    public function render()
    {
        return view('livewire.hr.settings-hr');
    }
}
