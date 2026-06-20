<?php

namespace App\Livewire\Rr;

use App\Models\Mpp;
use App\Models\Rr;
use App\Livewire\Rr\RrDataForm;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class RRForm
 * 
 * Komponen Livewire untuk formulir pembuatan & pengubahan Permintaan Rekrutmen (RR).
 * Mengelola pemilihan referensi Rencana Tenaga Kerja (MPP), menangani kolom yang hanya-baca (read-only), 
 * melakukan validasi sisa kuota, serta memproses penyimpanan data ke tabel RR.
 *
 * @package App\Livewire\Rr
 */
#[Layout('layouts.hr')]
class RRForm extends Component
{
    /** @var RrDataForm Objek formulir khusus (Livewire Form) untuk menampung isian data RR. */
    public RrDataForm $form;

    /** @var int|null ID MPP yang dipilih (referensi rencana kerja). */
    public $selectedMppId;

    /** @var int|null ID RR yang sedang diubah (null jika mode tambah baru). */
    public $vacancyId;

    /** @var bool Penanda apakah formulir saat ini dalam mode Edit. */
    public $isEdit = false;

    /** @var bool Status form read-only (berlaku ketika mengedit atau MPP sudah terpilih). */
    public $isReadOnly = false;

    // ==========================================
    // KOLOM REFERENSI MPP (Hanya-Baca)
    // ==========================================

    /** @var string Jabatan bawaan dari MPP. */
    public $job_title;

    /** @var string Departemen pengaju dari MPP. */
    public $department;

    /** @var int|string|null Estimasi gaji minimal dari MPP. */
    public $estimated_salary_min;

    /** @var int|string|null Estimasi gaji maksimal dari MPP. */
    public $estimated_salary_max;

    /** @var string|null Target tanggal karyawan masuk berdasarkan SLA MPP. */
    public $expected_join_date;

    /**
     * Inisialisasi awal formulir saat komponen dimuat.
     * Mengatur status Edit/Tambah dan memvalidasi akses (misal: jika RR sudah berjalan, tidak bisa diedit).
     *
     * @param int|null $mppId Parameter ID MPP dari URL (opsional).
     * @param int|null $id Parameter ID RR (jika mode edit).
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function mount($mppId = null, $id = null)
    {
        // Positional parameter binding workaround:
        // /rrs/{id}/edit has a single parameter 'id' which binds to $mppId.
        if (request()->routeIs('rr.edit')) {
            $id = $mppId;
            $mppId = null;
        }

        // Rute edit: jika $id disediakan
        if ($id) {
            $rr = Rr::findOrFail($id);

            // Logika rr dapat diedit ketika tidak berada di status active (Published), dan closed/completed.
            if ($rr->status === \App\Enums\RrStatus::PUBLISHED || $rr->status === \App\Enums\RrStatus::COMPLETED || $rr->status === \App\Enums\RrStatus::CLOSED || $rr->hiredCount() > 0) {
                session()->flash('error', 'Recruitment Request yang sedang aktif, selesai, atau memiliki pelamar tidak dapat diedit.');
                return redirect()->route('rr.index');
            }

            $this->vacancyId = $rr->id;
            $this->isEdit = true;
            $this->isReadOnly = true;
            $this->selectedMppId = $rr->mpp_id;

            // Populate MPP read-only fields
            $this->job_title = $rr->job_title;
            $this->department = $rr->department;
            $this->estimated_salary_min = $rr->estimated_salary_min;
            $this->estimated_salary_max = $rr->estimated_salary_max;
            $this->expected_join_date = $rr->expected_join_date ? $rr->expected_join_date->format('Y-m-d') : null;

            // Populate HR editable fields to form object
            $this->form->title = $rr->title;
            $this->form->quota = $rr->quota;
            $this->form->job_description = $rr->job_description;
            $this->form->job_requirements = $rr->job_requirements;
            $this->form->employment_type = $rr->employment_type;
            $this->form->location = $rr->location;
            $this->form->application_deadline = $rr->application_deadline ? $rr->application_deadline->format('Y-m-d') : null;
            $this->form->show_salary = $rr->show_salary;
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

            // Validasi: rr yang lain closed/completed dibawah mpp yang sama
            $hasActiveRr = Rr::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Closed')
                ->where('status', '!=', 'Completed')
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
     * Mengisi properti hanya-baca (read-only) pada tampilan formulir 
     * menggunakan data dari objek MPP yang dipilih.
     *
     * @param Mpp $mpp Objek Manpower Planning.
     * @return void
     */
    protected function populateMppFields(Mpp $mpp)
    {
        $this->job_title = $mpp->job_title;
        $this->department = $mpp->department;
        $this->estimated_salary_min = $mpp->estimated_salary_min;
        $this->estimated_salary_max = $mpp->estimated_salary_max;
        $this->expected_join_date = $mpp->absolute_target_date ? $mpp->absolute_target_date->format('Y-m-d') : null;
        $this->form->quota = $mpp->sisaKuota();
    }

    /**
     * Mengosongkan properti tampilan yang berasal dari MPP (reset kolom).
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
        $this->form->quota = null;
    }

    /**
     * Kejadian (event) yang memicu secara otomatis ketika pilihan dropdown MPP diubah oleh pengguna.
     * Melakukan pengecekan sisa kuota dan apakah masih ada RR aktif lainnya untuk MPP tersebut.
     *
     * @param int|null $value ID MPP yang baru dipilih.
     * @return void
     */
    public function updatedSelectedMppId($value)
    {
        if ($value) {
            $mpp = Mpp::findOrFail($value);
            
            // Validasi sisa kuota & RR aktif
            $remainingQuota = $mpp->sisaKuota();
            $hasActiveRr = Rr::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Closed')
                ->where('status', '!=', 'Completed')
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
     * Memvalidasi input dan menyimpan data Recruitment Request (RR) ke dalam database.
     * Juga menangani sinkronisasi kuota apabila RR ini sudah memiliki lowongan publik (Vacancy).
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

        // Lakukan validasi input via form object
        $this->form->validateWithMaxQuota($maxKuota);

        if ($this->isEdit) {
            $rr = Rr::findOrFail($this->vacancyId);

            // Double check edit permission sebelum disimpan
            // Cegah ubah data jika RR sudah Publish, Completed, atau punya Hired
            if ($rr->status->value === 'Published' || $rr->status->value === 'Completed' || $rr->status->value === 'Closed' || $rr->hiredCount() > 0) {
                $this->addError('general', 'Tidak dapat mengubah data RR yang sudah dipublikasi, selesai, atau memiliki kandidat.');
                return redirect()->route('rr.index');
            }

            $rr->update([
                'title' => $this->form->title,
                'job_description' => $this->form->job_description,
                'job_requirements' => $this->form->job_requirements ?: '',
                'employment_type' => $this->form->employment_type,
                'location' => $this->form->location,
                'application_deadline' => $this->form->application_deadline,
                'show_salary' => $this->form->show_salary ? true : false,
                'quota' => $this->form->quota,
            ]);

            // Sync kuota ke vacancy jika sudah publish
            if ($rr->vacancy) {
                $rr->vacancy->update([
                    'title' => $this->form->title,
                    'quota' => $this->form->quota
                ]);
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
            $hasActiveRr = Rr::where('mpp_id', $mpp->id)
                ->where('status', '!=', 'Closed')
                ->where('status', '!=', 'Completed')
                ->exists();
            if ($hasActiveRr) {
                session()->flash('error', 'Manpower Planning ini masih memiliki Recruitment Request yang aktif.');
                return redirect()->route('rr.index');
            }

            // Simpan RR baru sebagai Draft
            Rr::create([
                'mpp_id' => $mpp->id,
                'title' => $this->form->title,
                'job_title' => $mpp->job_title,
                'department' => $mpp->department,
                'estimated_salary_min' => $mpp->getRawOriginal('estimated_salary_min'),
                'estimated_salary_max' => $mpp->getRawOriginal('estimated_salary_max'),
                'expected_join_date' => $mpp->absolute_target_date,
                'job_description' => $this->form->job_description,
                'job_requirements' => $this->form->job_requirements ?: '',
                'employment_type' => $this->form->employment_type,
                'location' => $this->form->location,
                'application_deadline' => $this->form->application_deadline,
                'show_salary' => $this->form->show_salary ? true : false,
                'status' => 'Ready to Publish',
                'quota' => $this->form->quota,
            ]);

            session()->flash('message', 'Recruitment Request berhasil dibuat dan berstatus Ready to Publish.');
        }

        return redirect()->route('rr.index');
    }

    /**
     * Merender antarmuka formulir RR.
     * Menyiapkan daftar *dropdown* pilihan MPP yang memenuhi syarat (Sudah Disetujui & Kuota > 0).
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Ambil semua MPP Approved yang tidak memiliki RR aktif
        $query = Mpp::where('status', \App\Enums\MppStatus::APPROVED)
            ->whereDoesntHave('rrs', function ($query) {
                $query->where('status', '!=', 'Closed')
                      ->where('status', '!=', 'Completed');
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
