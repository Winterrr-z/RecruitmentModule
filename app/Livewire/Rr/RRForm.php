<?php

namespace App\Livewire\Rr;

use App\Models\Mpp;
use App\Models\RecruitmentRequest;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class RRForm
 * 
 * Komponen Livewire untuk form pembuatan & pengeditan Recruitment Request (RR) Baru.
 * Menangani pemilihan MPP, field read-only, validasi kuota sisa, pengeditan draft yang tidak memiliki pelamar, dan penyimpanan ke database.
 *
 * @package App\Livewire
 */
#[Layout('layouts.app')]
class RRForm extends Component
{
    /**
     * @var int|null ID MPP terpilih.
     */
    public $selectedMppId;

    /**
     * @var int|null ID RR yang sedang diedit.
     */
    public $lowonganId;

    /**
     * @var bool Menandakan apakah sedang dalam mode edit.
     */
    public $isEdit = false;

    /**
     * @var bool Status form read-only (dikunci dari parameter query).
     */
    public $isReadOnly = false;

    // Field MPP (Read-only)
    public $job_title;
    public $department;
    public $estimated_salary_min;
    public $estimated_salary_max;
    public $expected_join_date;
    public $quota; // Read-only

    // Field diisi HR
    public $job_description;
    public $job_requirements;
    public $employment_type = 'full-time'; // Default
    public $location = 'remote'; // Default
    public $application_deadline;
    public $show_salary = false;

    /**
     * Inisialisasi komponen.
     *
     * @param int|null $mppId
     * @param int|null $id
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function mount($mppId = null, $id = null)
    {
        // Rute edit: jika $id disediakan
        if ($id) {
            $rr = RecruitmentRequest::findOrFail($id);

            // Logika rr dapat diedit ketika tidak berada di status active (Published), dan closed/completed.
            if ($rr->status->value === 'Published' || $rr->status->value === 'Completed/Closed' || $rr->hiredCount() > 0) {
                session()->flash('error', 'Recruitment Request yang sedang aktif, selesai, atau memiliki pelamar tidak dapat diedit.');
                return redirect()->route('rr.index');
            }

            $this->lowonganId = $rr->id;
            $this->isEdit = true;
            $this->isReadOnly = true;
            $this->selectedMppId = $rr->mpp_id;

            // Populate MPP read-only fields
            $this->job_title = $rr->job_title;
            $this->department = $rr->department;
            $this->quota = $rr->quota;
            $this->estimated_salary_min = $rr->estimated_salary_min;
            $this->estimated_salary_max = $rr->estimated_salary_max;
            $this->expected_join_date = $rr->expected_join_date ? $rr->expected_join_date->format('Y-m-d') : null;

            // Populate HR editable fields
            $this->job_description = $rr->job_description;
            $this->job_requirements = $rr->job_requirements;
            $this->employment_type = $rr->employment_type;
            $this->location = $rr->location;
            $this->application_deadline = $rr->application_deadline ? $rr->application_deadline->format('Y-m-d') : null;
            $this->show_salary = $rr->show_salary;
            return;
        }

        // Fallback mengambil mppId dari query string jika tidak disediakan lewat parameter route
        if (!$mppId) {
            $mppId = request()->query('mpp_id');
        }

        if ($mppId) {
            $mpp = Mpp::findOrFail($mppId);

            // Validasi status approved
            if ($mpp->status !== \App\Enums\MppStatus::APPROVED) {
                session()->flash('error', 'Hanya Manpower Planning yang telah disetujui (Approved) yang dapat dibuatkan recruitment request.');
                return redirect()->route('rr.index');
            }

            // Validasi sisa kuota
            $remainingQuota = $mpp->sisaKuota();
            if ($remainingQuota <= 0) {
                session()->flash('error', 'Manpower Planning ini sudah memenuhi seluruh kuota kebutuhan.');
                return redirect()->route('rr.index');
            }

            // Validasi: rr yang lain completed/closed dibawah mpp yang sama
            $hasActiveRr = RecruitmentRequest::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Completed/Closed')
                ->exists();
            if ($hasActiveRr) {
                session()->flash('error', 'Manpower Planning ini masih memiliki Recruitment Request yang aktif.');
                return redirect()->route('rr.index');
            }

            $this->selectedMppId = $mpp->id;
            $this->isReadOnly = true;
            $this->populateMppFields($mpp);
        }
    }

    /**
     * Mengisi properti read-only dari objek MPP.
     *
     * @param Mpp $mpp
     * @return void
     */
    protected function populateMppFields(Mpp $mpp)
    {
        $this->job_title = $mpp->job_title;
        $this->department = $mpp->department;
        $this->estimated_salary_min = $mpp->estimated_salary_min;
        $this->estimated_salary_max = $mpp->estimated_salary_max;
        $this->expected_join_date = $mpp->absolute_target_date ? $mpp->absolute_target_date->format('Y-m-d') : null;
        $this->quota = $mpp->sisaKuota();
    }

    /**
     * Mengosongkan properti mpp.
     *
     * @return void
     */
    protected function clearMppFields()
    {
        $this->job_title = null;
        $this->department = null;
        $this->estimated_salary_min = null;
        $this->estimated_salary_max = null;
        $this->expected_join_date = null;
        $this->quota = null;
    }

    /**
     * Event handler ketika pilihan dropdown MPP diubah.
     *
     * @param int|null $value
     * @return void
     */
    public function updatedSelectedMppId($value)
    {
        if ($value) {
            $mpp = Mpp::findOrFail($value);
            
            // Validasi sisa kuota & RR aktif
            $remainingQuota = $mpp->sisaKuota();
            $hasActiveRr = RecruitmentRequest::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Completed/Closed')
                ->exists();

            if ($remainingQuota <= 0 || $hasActiveRr) {
                $this->clearMppFields();
                $this->selectedMppId = null;
                $this->addError('selectedMppId', 'Manpower Planning tidak dapat digunakan karena kuota habis atau memiliki RR aktif.');
                return;
            }

            $this->populateMppFields($mpp);
        } else {
            $this->clearMppFields();
        }
    }

    /**
     * Simpan data RR ke database.
     *
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        if (!$this->selectedMppId) {
            $this->addError('selectedMppId', 'Pilih Manpower Planning terlebih dahulu.');
            return;
        }

        $mpp = Mpp::findOrFail($this->selectedMppId);
        $maxKuota = $mpp->sisaKuota();

        // Lakukan validasi input
        $this->validate([
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

        if ($this->isEdit) {
            $rr = RecruitmentRequest::findOrFail($this->lowonganId);

            // Double check edit permission sebelum disimpan
            if ($rr->status->value === 'Published' || $rr->status->value === 'Completed/Closed' || $rr->hiredCount() > 0) {
                session()->flash('error', 'Recruitment Request yang sedang aktif, selesai, atau memiliki pelamar tidak dapat diedit.');
                return redirect()->route('rr.index');
            }

            $rr->update([
                'job_description' => $this->job_description,
                'job_requirements' => $this->job_requirements ?: '',
                'employment_type' => $this->employment_type,
                'location' => $this->location,
                'application_deadline' => $this->application_deadline,
                'show_salary' => $this->show_salary ? true : false,
                'quota' => $this->quota,
            ]);

            // Sync kuota ke lowongan jika sudah publish
            if ($rr->lowongan) {
                $rr->lowongan->update(['quota' => $this->quota]);
            }

            session()->flash('message', 'Recruitment Request berhasil diperbarui.');
        } else {
            $mpp = Mpp::findOrFail($this->selectedMppId);

            // Validasi status approved
            if ($mpp->status !== \App\Enums\MppStatus::APPROVED) {
                session()->flash('error', 'Hanya Manpower Planning yang telah disetujui (Approved) yang dapat dibuatkan RR.');
                return redirect()->route('rr.index');
            }

            // Validasi sisa kuota sebelum disubmit
            $remainingQuota = $mpp->sisaKuota();
            if ($remainingQuota <= 0) {
                session()->flash('error', 'Manpower Planning ini sudah memenuhi seluruh kuota kebutuhan.');
                return redirect()->route('rr.index');
            }

            // Validasi RR aktif yang lain sebelum disubmit
            $hasActiveRr = RecruitmentRequest::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Completed/Closed')
                ->exists();
            if ($hasActiveRr) {
                session()->flash('error', 'Manpower Planning ini masih memiliki Recruitment Request yang aktif.');
                return redirect()->route('rr.index');
            }

            // Simpan RR baru sebagai Draft
            RecruitmentRequest::create([
                'mpp_id' => $mpp->id,
                'job_title' => $mpp->job_title,
                'department' => $mpp->department,
                'estimated_salary_min' => $mpp->estimated_salary_min,
                'estimated_salary_max' => $mpp->estimated_salary_max,
                'expected_join_date' => $mpp->absolute_target_date,
                'job_description' => $this->job_description,
                'job_requirements' => $this->job_requirements ?: '',
                'employment_type' => $this->employment_type,
                'location' => $this->location,
                'application_deadline' => $this->application_deadline,
                'show_salary' => $this->show_salary ? true : false,
                'status' => 'Ready to Publish',
                'quota' => $this->quota,
            ]);

            session()->flash('message', 'Recruitment Request berhasil dibuat dan berstatus Ready to Publish.');
        }

        return redirect()->route('rr.index');
    }

    /**
     * Render komponen Livewire.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Ambil semua MPP Approved yang tidak memiliki RR aktif (selain Completed/Closed)
        $query = Mpp::where('status', \App\Enums\MppStatus::APPROVED)
            ->whereDoesntHave('recruitmentRequests', function ($query) {
                $query->where('status', '!=', \App\Enums\RrStatus::COMPLETED_CLOSED);
            });

        // Jika sedang edit, masukkan MPP yang terikat saat ini agar opsi dropdown tidak kosong/error
        if ($this->isEdit && $this->selectedMppId) {
            $query->orWhere('id', $this->selectedMppId);
        }

        // Ambil semua hasil query, lalu filter sisa kuota harus > 0 di sisi PHP
        $mppsDropdown = $query->get()->filter(function ($mpp) {
            // Khusus MPP terpilih saat edit, selalu lolos agar select tetap terikat
            if ($this->isEdit && $mpp->id === $this->selectedMppId) {
                return true;
            }
            return $mpp->sisaKuota() > 0;
        });

        return view('livewire.rr.rr-form', [
            'mppsDropdown' => $mppsDropdown
        ]);
    }
}
