<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class MppForm
 * 
 * Komponen Livewire untuk merender antarmuka pengisian (pembuatan) atau pengubahan (edit) 
 * Manpower Planning (MPP).
 *
 * @package App\Livewire\Mpp
 */
#[Layout('layouts.hr')]
class MppForm extends Component
{
    /** @var \App\Livewire\Mpp\MppDataForm Objek formulir Livewire khusus MPP. */
    public MppDataForm $form;

    /** @var int|null ID MPP yang sedang diedit (null jika mode Create). */
    public $mppId = null;
    
    /** @var bool Penanda apakah form saat ini dalam mode Edit. */
    public $isEdit = false;

    /**
     * Memuat data saat komponen pertama kali dirender.
     * Mengisi data ke objek form jika dalam mode Edit dan mengecek apakah 
     * MPP tersebut masih diperbolehkan untuk diubah.
     *
     * @param int|null $id ID MPP (jika ada).
     */
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

    /**
     * Otomatis memicu perhitungan ulang batas waktu target
     * setiap kali nilai "SLA Hari" diubah di formulir.
     */
    public function updatedFormSlaDays()
    {
        $this->form->calculateTargetWaktu();
    }

    /**
     * Menyimpan data MPP (Buat Baru atau Perbarui).
     * Mencegah pembaruan jika status MPP sudah 'Closed' atau 'Completed'.
     */
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

    /**
     * Render komponen antarmuka formulir MPP.
     */
    public function render()
    {
        return view('livewire.mpp.mpp-form');
    }
}
