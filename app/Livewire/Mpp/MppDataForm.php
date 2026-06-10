<?php

namespace App\Livewire\Mpp;

use Livewire\Form;
use App\Models\Mpp;
use Carbon\Carbon;

class MppDataForm extends Form
{
    public ?Mpp $mpp = null;

    public $plan_name;
    public $department;
    public $job_title;
    public $quota = 1;
    public $estimated_salary_min;
    public $estimated_salary_max;
    public $sla_days;
    public $absolute_target_date;
    public $note;

    public function rules(): array
    {
        return [
            'plan_name' => 'required|string|max:200',
            'department' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'quota' => 'required|integer|min:1',
            'estimated_salary_min' => 'nullable|integer|min:0',
            'estimated_salary_max' => 'nullable|integer' . ($this->estimated_salary_min ? '|gt:estimated_salary_min' : '|min:0'),
            'sla_days' => 'required|integer|min:1',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_name.required' => 'Nama Plan wajib diisi.',
            'department.required' => 'Departemen wajib diisi.',
            'job_title.required' => 'Jabatan wajib diisi.',
            'quota.required' => 'Jumlah Kebutuhan wajib diisi.',
            'quota.min' => 'Jumlah Kebutuhan minimal 1 Orang.',
            'estimated_salary_max.gt' => 'Estimasi Gaji Max harus lebih besar dari Gaji Min.',
            'sla_days.required' => 'SLA wajib diisi.',
            'sla_days.min' => 'SLA minimal 1 hari.',
        ];
    }

    public function setMpp(Mpp $mpp)
    {
        $this->mpp = $mpp;
        $this->plan_name = $mpp->plan_name;
        $this->department = $mpp->department;
        $this->job_title = $mpp->job_title;
        $this->quota = $mpp->quota;
        
        $this->estimated_salary_min = $mpp->estimated_salary_min;
        $this->estimated_salary_max = $mpp->estimated_salary_max;
        
        $this->sla_days = $mpp->sla_days;
        $this->absolute_target_date = $mpp->absolute_target_date ? $mpp->absolute_target_date->format('Y-m-d') : null;
        $this->note = $mpp->note;
    }

    public function calculateTargetWaktu()
    {
        if (is_numeric($this->sla_days) && $this->sla_days > 0) {
            $this->absolute_target_date = now()->addDays((int)$this->sla_days)->format('Y-m-d');
        } else {
            $this->absolute_target_date = null;
        }
    }

    public function store()
    {
        $this->calculateTargetWaktu();

        $rawMin = $this->estimated_salary_min;
        $rawMax = $this->estimated_salary_max;

        if (is_string($this->estimated_salary_min)) {
            $cleanMin = preg_replace('/\D/', '', $this->estimated_salary_min);
            $this->estimated_salary_min = $cleanMin !== '' ? (int)$cleanMin : null;
        }
        if (is_string($this->estimated_salary_max)) {
            $cleanMax = preg_replace('/\D/', '', $this->estimated_salary_max);
            $this->estimated_salary_max = $cleanMax !== '' ? (int)$cleanMax : null;
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->estimated_salary_min = $rawMin;
            $this->estimated_salary_max = $rawMax;
            throw $e;
        }

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
        ];

        if ($this->mpp) {
            $this->mpp->update($data);
        } else {
            Mpp::create($data);
        }
    }
}
