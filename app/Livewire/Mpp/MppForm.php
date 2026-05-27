<?php

namespace App\Livewire\Mpp;

use App\Models\Mpp;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class MppForm
 * 
 * Component for creating and editing Manpower Planning (MPP).
 */
#[Layout('layouts.app')]
class MppForm extends Component
{
    /** @var int|null ID MPP yang sedang diedit (null jika mode Create). */
    public $mppId = null;
    
    /** @var bool Penanda apakah form saat ini dalam mode Edit. */
    public $isEdit = false;

    // Form fields
    public $nama_plan;
    public $departemen;
    public $jabatan;
    public $jumlah_kebutuhan = 1;
    public $estimasi_gaji_min;
    public $estimasi_gaji_max;
    public $sla_hari;
    public $target_waktu_absolut;
    public $note;

    public function mount($id = null)
    {
        $this->mppId = $id;
        
        if ($this->mppId) {
            $this->isEdit = true;
            $mpp = Mpp::findOrFail($this->mppId);
            
            $status = $mpp->getComputedStatus();
            if ($status === 'Closed' || $status === 'Filled') {
                session()->flash('error', 'Tidak dapat mengubah MPP plan yang sudah closed atau filled.');
                return redirect()->route('mpp.index');
            }

            $this->nama_plan = $mpp->nama_plan;
            $this->departemen = $mpp->departemen;
            $this->jabatan = $mpp->jabatan;
            $this->jumlah_kebutuhan = $mpp->jumlah_kebutuhan;
            
            // Format integers from DB with commas
            $this->estimasi_gaji_min = $mpp->estimasi_gaji_min ? number_format($mpp->estimasi_gaji_min, 0, '.', ',') : null;
            $this->estimasi_gaji_max = $mpp->estimasi_gaji_max ? number_format($mpp->estimasi_gaji_max, 0, '.', ',') : null;
            
            $this->sla_hari = $mpp->sla_hari;
            $this->target_waktu_absolut = $mpp->target_waktu_absolut ? $mpp->target_waktu_absolut->format('Y-m-d') : null;
            $this->note = $mpp->note;
        }
    }

    public function updatedSlaHari()
    {
        $this->calculateTargetWaktu();
    }

    public function updatedEstimasiGajiMin($value)
    {
        $this->estimasi_gaji_min = $this->formatNumber($value);
    }

    public function updatedEstimasiGajiMax($value)
    {
        $this->estimasi_gaji_max = $this->formatNumber($value);
    }

    protected function formatNumber($value)
    {
        if (empty($value)) return null;
        $clean = preg_replace('/\D/', '', $value);
        return $clean !== '' ? number_format((int)$clean, 0, '.', ',') : null;
    }

    protected function getNumericSalary($value)
    {
        if (empty($value)) return null;
        $clean = preg_replace('/\D/', '', $value);
        return $clean !== '' ? (int)$clean : null;
    }

    protected function calculateTargetWaktu()
    {
        if (is_numeric($this->sla_hari) && $this->sla_hari > 0) {
            $this->target_waktu_absolut = now()->addDays((int)$this->sla_hari)->format('Y-m-d');
        } else {
            $this->target_waktu_absolut = null;
        }
    }

    public function save()
    {
        // Keep formatted copies in case validation fails
        $formattedMin = $this->estimasi_gaji_min;
        $formattedMax = $this->estimasi_gaji_max;

        // Temporarily strip commas for validation
        $this->estimasi_gaji_min = $this->getNumericSalary($this->estimasi_gaji_min);
        $this->estimasi_gaji_max = $this->getNumericSalary($this->estimasi_gaji_max);

        try {
            $this->validate([
                'nama_plan' => 'required|string|max:200',
                'departemen' => 'required|string|max:100',
                'jabatan' => 'required|string|max:100',
                'jumlah_kebutuhan' => 'required|integer|min:1',
                'estimasi_gaji_min' => 'nullable|integer|min:0',
                'estimasi_gaji_max' => 'nullable|integer' . ($this->estimasi_gaji_min ? '|gt:estimasi_gaji_min' : '|min:0'),
                'sla_hari' => 'required|integer|min:1',
                'note' => 'nullable|string|max:1000',
            ], [
                'nama_plan.required' => 'Nama Plan wajib diisi.',
                'departemen.required' => 'Departemen wajib diisi.',
                'jabatan.required' => 'Jabatan wajib diisi.',
                'jumlah_kebutuhan.required' => 'Jumlah Kebutuhan wajib diisi.',
                'jumlah_kebutuhan.min' => 'Jumlah Kebutuhan minimal 1 Orang.',
                'estimasi_gaji_max.gt' => 'Estimasi Gaji Max harus lebih besar dari Gaji Min.',
                'sla_hari.required' => 'SLA wajib diisi.',
                'sla_hari.min' => 'SLA minimal 1 hari.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Restore formatted copies
            $this->estimasi_gaji_min = $formattedMin;
            $this->estimasi_gaji_max = $formattedMax;
            throw $e;
        }

        $this->calculateTargetWaktu();

        $data = [
            'nama_plan' => $this->nama_plan,
            'departemen' => $this->departemen,
            'jabatan' => $this->jabatan,
            'jumlah_kebutuhan' => $this->jumlah_kebutuhan,
            'estimasi_gaji_min' => $this->estimasi_gaji_min,
            'estimasi_gaji_max' => $this->estimasi_gaji_max,
            'sla_hari' => $this->sla_hari,
            'target_waktu_absolut' => $this->target_waktu_absolut,
            'note' => $this->note ?: null,
            'last_activity_at' => now(),
            'syarat_pendidikan' => 'Minimal D3',
            'syarat_pengalaman' => 'Minimal 1 Tahun',
            'keahlian' => [],
        ];

        if ($this->isEdit) {
            $mpp = Mpp::findOrFail($this->mppId);
            $status = $mpp->getComputedStatus();
            if ($status === 'Closed' || $status === 'Filled') {
                session()->flash('error', 'Tidak dapat mengubah MPP plan yang sudah closed atau filled.');
                return redirect()->route('mpp.index');
            }
            $mpp->update($data);
            session()->flash('message', 'Manpower Plan berhasil diperbarui.');
        } else {
            Mpp::create($data);
            session()->flash('message', 'Manpower Plan berhasil dibuat.');
        }

        return redirect()->route('mpp.index');
    }

    public function render()
    {
        return view('livewire.mpp.mpp-form');
    }
}
