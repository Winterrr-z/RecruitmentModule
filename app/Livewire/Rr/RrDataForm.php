<?php

namespace App\Livewire\Rr;

use Livewire\Form;

class RrDataForm extends Form
{
    public $job_description;
    public $job_requirements;
    public $employment_type = 'full-time';
    public $location = 'remote';
    public $application_deadline;
    public $show_salary = false;
    public $quota;

    /**
     * Validate the form fields with a dynamic max quota.
     *
     * @param int $maxKuota
     * @return array
     */
    public function validateWithMaxQuota($maxKuota)
    {
        return $this->validate([
            'job_description' => 'required|string|max:5000',
            'job_requirements' => 'nullable|string|max:5000',
            'employment_type' => 'required|in:full-time,contract',
            'location' => 'required|in:remote,on-site',
            'application_deadline' => 'required|date|after_or_equal:today',
            'quota' => 'required|integer|min:1|max:' . $maxKuota,
        ], [
            'job_description.required' => 'Deskripsi Pekerjaan wajib diisi.',
            'employment_type.required' => 'Tipe Kerja wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'application_deadline.required' => 'Application Deadline wajib diisi.',
            'application_deadline.after_or_equal' => 'Application Deadline minimal hari ini.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota minimal 1.',
            'quota.max' => 'Kuota tidak boleh melebihi sisa kebutuhan MPP (' . $maxKuota . ').',
        ]);
    }
}
