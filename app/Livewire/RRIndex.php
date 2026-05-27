<?php

namespace App\Livewire;

use App\Models\Lowongan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

/**
 * Class RRIndex
 * 
 * Komponen Livewire untuk menampilkan daftar Recruitment Request (RR) / Lowongan.
 * Menangani pencarian, filter status, pagination, serta aksi publish dan close lowongan.
 *
 * @package App\Livewire
 */
class RRIndex extends Component
{
    use WithPagination;

    /**
     * @var string Query pencarian jabatan atau departemen.
     */
    #[Url]
    public $search = '';

    /**
     * @var string Filter status lowongan.
     */
    #[Url]
    public $status = '';

    /**
     * Reset pagination page ketika pencarian atau filter diubah.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function updating($property, $value)
    {
        if (in_array($property, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    /**
     * Publikasikan lowongan (ubah status dari 'Ready to Publish' ke 'Published').
     *
     * @param int $id
     * @return void
     */
    public function publish($id)
    {
        $lowongan = Lowongan::findOrFail($id);
        if ($lowongan->status === 'Ready to Publish') {
            $lowongan->update(['status' => 'Published']);
            session()->flash('message', 'Lowongan "' . $lowongan->jabatan . '" berhasil dipublikasikan.');
        }
    }

    /**
     * Tutup lowongan (ubah status dari 'Published' ke 'Completed/Closed').
     *
     * @param int $id
     * @return void
     */
    public function close($id)
    {
        $lowongan = Lowongan::findOrFail($id);
        if ($lowongan->status === 'Published') {
            $lowongan->update(['status' => 'Completed/Closed']);
            session()->flash('message', 'Lowongan "' . $lowongan->jabatan . '" berhasil ditutup.');
        }
    }

    /**
     * Render komponen Livewire.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Hitung statistik untuk overview
        $stats = [
            'total_active' => Lowongan::where('status', 'Published')->count(),
            'ready_to_publish' => Lowongan::where('status', 'Ready to Publish')->count(),
            'completed' => Lowongan::where('status', 'Completed/Closed')->count(),
        ];

        // Query lowongans dengan filter
        $query = Lowongan::query();

        if ($this->status !== '') {
            if ($this->status === 'Completed/Closed') {
                $query->whereIn('status', ['Completed/Closed', 'Completed', 'Closed', 'completed', 'closed']);
            } else {
                $query->whereRaw('lower(status) = ?', [strtolower($this->status)]);
            }
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('jabatan', 'like', '%' . $this->search . '%')
                  ->orWhere('departemen', 'like', '%' . $this->search . '%');
            });
        }

        $lowongans = $query->latest()->paginate(10);

        return view('livewire.rr-index', [
            'lowongans' => $lowongans,
            'stats' => $stats
        ])->layout('layouts.app');
    }
}
