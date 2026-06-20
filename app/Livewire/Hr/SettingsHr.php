<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class SettingsHr
 *
 * Komponen Livewire untuk menampilkan halaman pengaturan HR.
 * Halaman ini dapat dikembangkan untuk konfigurasi umum aplikasi rekrutmen.
 *
 * @package App\Livewire\Hr
 */
#[Layout('layouts.hr')]
class SettingsHr extends Component
{
    /**
     * Render antarmuka komponen dengan layout HR.
     */
    public function render()
    {
        return view('livewire.hr.settings-hr');
    }
}
