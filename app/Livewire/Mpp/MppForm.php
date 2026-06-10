<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class MppForm
 * 
 * Component for creating and editing Manpower Planning (MPP).
 */
#[Layout('layouts.hr')]
class MppForm extends Component
{
    public MppDataForm $form;

    /** @var int|null ID MPP yang sedang diedit (null jika mode Create). */
    public $mppId = null;
    
    /** @var bool Penanda apakah form saat ini dalam mode Edit. */
    public $isEdit = false;

    public function mount($id = null)
    {
        $this->mppId = $id;
        
        if ($this->mppId) {
            $this->isEdit = true;
            $mpp = Mpp::findOrFail($this->mppId);
            
            $status = $mpp->getComputedStatus();
            if ($status === 'Closed' || $status === 'Completed') {
                session()->flash('error', 'Tidak dapat mengubah MPP plan yang sudah closed atau completed.');
                return redirect()->route('mpp.index');
            }

            $this->form->setMpp($mpp);
        }
    }

    public function updatedFormSlaDays()
    {
        $this->form->calculateTargetWaktu();
    }

    public function save()
    {
        if ($this->isEdit) {
            $mpp = Mpp::findOrFail($this->mppId);
            $status = $mpp->getComputedStatus();
            if ($status === 'Closed' || $status === 'Completed') {
                session()->flash('error', 'Tidak dapat mengubah MPP plan yang sudah closed atau completed.');
                return redirect()->route('mpp.index');
            }
        }

        $this->form->store();

        if ($this->isEdit) {
            session()->flash('message', 'Manpower Plan berhasil diperbarui.');
        } else {
            session()->flash('message', 'Manpower Plan berhasil dibuat.');
        }

        return redirect()->route('mpp.index');
    }

    public function render()
    {
        return view('livewire.mpp.mpp-form');
    }
}
