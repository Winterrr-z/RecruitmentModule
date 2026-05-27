<?php

namespace App\Livewire\Mpp;

use Livewire\Component;

/**
 * Class MppForm
 * 
 * Placeholder component for Mpp Form (create/edit).
 */
class MppForm extends Component
{
    public $mppId = null;

    public function mount($id = null)
    {
        $this->mppId = $id;
    }

    public function render()
    {
        return '<div>Mpp Form Placeholder</div>';
    }
}
