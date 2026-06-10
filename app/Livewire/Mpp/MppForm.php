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
    /** @var int|null ID MPP yang sedang diedit (null jika mode Create). */
    public $mppId = null;
    
    /** @var bool Penanda apakah form saat ini dalam mode Edit. */
    public $isEdit = false;

    // Form fields
    public $plan_name;
    public $department;
    public $job_title;
    public $quota = 1;
    public $estimated_salary_min;
    public $estimated_salary_max;
    public $sla_days;
    public $absolute_target_date;
    public $note;

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

            $this->plan_name = $mpp->plan_name;
            $this->department = $mpp->department;
            $this->job_title = $mpp->job_title;
            $this->quota = $mpp->quota;
            
            // Format integers from DB with commas
            $this->estimated_salary_min = $mpp->estimated_salary_min ? number_format($mpp->estimated_salary_min, 0, '.', ',') : null;
            $this->estimated_salary_max = $mpp->estimated_salary_max ? number_format($mpp->estimated_salary_max, 0, '.', ',') : null;
            
            $this->sla_days = $mpp->sla_days;
            $this->absolute_target_date = $mpp->absolute_target_date ? $mpp->absolute_target_date->format('Y-m-d') : null;
            $this->note = $mpp->note;
        }
    }

    public function updatedSlaHari()
    {
        $this->calculateTargetWaktu();
    }

    public function updatedEstimasiGajiMin($value)
    {
        $this->estimated_salary_min = $this->formatNumber($value);
    }

    public function updatedEstimasiGajiMax($value)
    {
        $this->estimated_salary_max = $this->formatNumber($value);
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
        if (is_numeric($this->sla_days) && $this->sla_days > 0) {
            $this->absolute_target_date = now()->addDays((int)$this->sla_days)->format('Y-m-d');
        } else {
            $this->absolute_target_date = null;
        }
    }

    public function save()
    {
        // Keep formatted copies in case validation fails
        $formattedMin = $this->estimated_salary_min;
        $formattedMax = $this->estimated_salary_max;

        // Temporarily strip commas for validation
        $this->estimated_salary_min = $this->getNumericSalary($this->estimated_salary_min);
        $this->estimated_salary_max = $this->getNumericSalary($this->estimated_salary_max);

        try {
            $this->validate([
                'plan_name' => 'required|string|max:200',
                'department' => 'required|string|max:100',
                'job_title' => 'required|string|max:100',
                'quota' => 'required|integer|min:1',
                'estimated_salary_min' => 'nullable|integer|min:0',
                'estimated_salary_max' => 'nullable|integer' . ($this->estimated_salary_min ? '|gt:estimated_salary_min' : '|min:0'),
                'sla_days' => 'required|integer|min:1',
                'note' => 'nullable|string|max:1000',
            ], [
                'plan_name.required' => 'Nama Plan wajib diisi.',
                'department.required' => 'Departemen wajib diisi.',
                'job_title.required' => 'Jabatan wajib diisi.',
                'quota.required' => 'Jumlah Kebutuhan wajib diisi.',
                'quota.min' => 'Jumlah Kebutuhan minimal 1 Orang.',
                'estimated_salary_max.gt' => 'Estimasi Gaji Max harus lebih besar dari Gaji Min.',
                'sla_days.required' => 'SLA wajib diisi.',
                'sla_days.min' => 'SLA minimal 1 hari.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Restore formatted copies
            $this->estimated_salary_min = $formattedMin;
            $this->estimated_salary_max = $formattedMax;
            throw $e;
        }

        $this->calculateTargetWaktu();

        $data = [
            'plan_name' => $this->plan_name,
            'department' => $this->department,
            'job_title' => $this->job_title,
            'quota' => $this->quota,
            'estimated_salary_min' => $this->estimated_salary_min,
            'estimated_salary_max' => $this->estimated_salary_max,
            'sla_days' => $this->sla_days,
            'absolute_target_date' => $this->absolute_target_date,
            'note' => $this->note ?: null,
            'last_activity_at' => now(),
            'syarat_pendidikan' => 'Minimal D3',
            'syarat_pengalaman' => 'Minimal 1 Tahun',
            'keahlian' => [],
        ];

        if ($this->isEdit) {
            $mpp = Mpp::findOrFail($this->mppId);
            $status = $mpp->getComputedStatus();
            if ($status === 'Closed' || $status === 'Completed') {
                session()->flash('error', 'Tidak dapat mengubah MPP plan yang sudah closed atau completed.');
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
