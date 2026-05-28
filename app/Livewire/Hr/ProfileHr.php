<?php

namespace App\Livewire\Hr;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Class ProfileHr
 *
 * Komponen read-only untuk menampilkan detail profil pengguna HR.
 *
 * @package App\Livewire\Hr
 */
class ProfileHr extends Component
{
    /**
     * Render komponen dengan layout HR (layouts.app).
     */
    public function render()
    {
        return view('livewire.hr.profile-hr', [
            'user' => Auth::user(),
        ])->layout('layouts.app');
    }
}
