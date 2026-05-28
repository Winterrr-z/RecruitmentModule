<?php

namespace App\Livewire\Rr;

use App\Models\Mpp;
use App\Models\RecruitmentRequest;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class RRForm
 * 
 * Komponen Livewire untuk form pembuatan & pengeditan Recruitment Request (RR) Baru.
 * Menangani pemilihan MPP, field read-only, validasi kuota sisa, pengeditan draft yang tidak memiliki pelamar, dan penyimpanan ke database.
 *
 * @package App\Livewire
 */
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
    public $jabatan;
    public $departemen;
    public $estimasi_gaji_min;
    public $estimasi_gaji_max;
    public $expected_join_date;
    public $kuota; // Read-only

    // Field diisi HR
    public $deskripsi_pekerjaan;
    public $spesifikasi_kebutuhan;
    public $tipe_kerja = 'full-time'; // Default
    public $lokasi = 'remote'; // Default
    public $application_deadline;
    public $tampilkan_gaji = false;

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
            if ($rr->status === 'Published' || $rr->status === 'Completed/Closed' || $rr->hiredCount() > 0) {
                session()->flash('error', 'Recruitment Request yang sedang aktif, selesai, atau memiliki pelamar tidak dapat diedit.');
                return redirect()->route('rr.index');
            }

            $this->lowonganId = $rr->id;
            $this->isEdit = true;
            $this->isReadOnly = true;
            $this->selectedMppId = $rr->mpp_id;

            // Populate MPP read-only fields
            $this->jabatan = $rr->jabatan;
            $this->departemen = $rr->departemen;
            $this->kuota = $rr->kuota;
            $this->estimasi_gaji_min = $rr->estimasi_gaji_min;
            $this->estimasi_gaji_max = $rr->estimasi_gaji_max;
            $this->expected_join_date = $rr->expected_join_date ? $rr->expected_join_date->format('Y-m-d') : null;

            // Populate HR editable fields
            $this->deskripsi_pekerjaan = $rr->deskripsi_pekerjaan;
            $this->spesifikasi_kebutuhan = $rr->spesifikasi_kebutuhan;
            $this->tipe_kerja = $rr->tipe_kerja;
            $this->lokasi = $rr->lokasi;
            $this->application_deadline = $rr->application_deadline ? $rr->application_deadline->format('Y-m-d') : null;
            $this->tampilkan_gaji = $rr->tampilkan_gaji;
            return;
        }

        // Fallback mengambil mppId dari query string jika tidak disediakan lewat parameter route
        if (!$mppId) {
            $mppId = request()->query('mpp_id');
        }

        if ($mppId) {
            $mpp = Mpp::findOrFail($mppId);

            // Validasi status approved
            if (strtolower($mpp->status) !== 'approved') {
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
        $this->jabatan = $mpp->jabatan;
        $this->departemen = $mpp->departemen;
        $this->estimasi_gaji_min = $mpp->estimasi_gaji_min;
        $this->estimasi_gaji_max = $mpp->estimasi_gaji_max;
        $this->expected_join_date = $mpp->target_waktu_absolut ? $mpp->target_waktu_absolut->format('Y-m-d') : null;
        $this->kuota = $mpp->sisaKuota();
    }

    /**
     * Mengosongkan properti mpp.
     *
     * @return void
     */
    protected function clearMppFields()
    {
        $this->jabatan = null;
        $this->departemen = null;
        $this->estimasi_gaji_min = null;
        $this->estimasi_gaji_max = null;
        $this->expected_join_date = null;
        $this->kuota = null;
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

        // Lakukan validasi input
        $this->validate([
            'deskripsi_pekerjaan' => 'required|string|max:5000',
            'spesifikasi_kebutuhan' => 'nullable|string|max:5000',
            'tipe_kerja' => 'required|in:full-time,contract',
            'lokasi' => 'required|in:remote,on-site',
            'application_deadline' => 'required|date|after_or_equal:today',
        ], [
            'deskripsi_pekerjaan.required' => 'Deskripsi Pekerjaan wajib diisi.',
            'tipe_kerja.required' => 'Tipe Kerja wajib diisi.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'application_deadline.required' => 'Application Deadline wajib diisi.',
            'application_deadline.after_or_equal' => 'Application Deadline minimal hari ini.',
        ]);

        if ($this->isEdit) {
            $rr = RecruitmentRequest::findOrFail($this->lowonganId);

            // Double check edit permission sebelum disimpan
            if ($rr->status === 'Published' || $rr->status === 'Completed/Closed' || $rr->hiredCount() > 0) {
                session()->flash('error', 'Recruitment Request yang sedang aktif, selesai, atau memiliki pelamar tidak dapat diedit.');
                return redirect()->route('rr.index');
            }

            $rr->update([
                'deskripsi_pekerjaan' => $this->deskripsi_pekerjaan,
                'spesifikasi_kebutuhan' => $this->spesifikasi_kebutuhan ?: '',
                'tipe_kerja' => $this->tipe_kerja,
                'lokasi' => $this->lokasi,
                'application_deadline' => $this->application_deadline,
                'tampilkan_gaji' => $this->tampilkan_gaji ? true : false,
            ]);

            session()->flash('message', 'Recruitment Request berhasil diperbarui.');
        } else {
            $mpp = Mpp::findOrFail($this->selectedMppId);

            // Validasi status approved
            if (strtolower($mpp->status) !== 'approved') {
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
                'jabatan' => $mpp->jabatan,
                'departemen' => $mpp->departemen,
                'estimasi_gaji_min' => $mpp->estimasi_gaji_min,
                'estimasi_gaji_max' => $mpp->estimasi_gaji_max,
                'expected_join_date' => $mpp->target_waktu_absolut,
                'deskripsi_pekerjaan' => $this->deskripsi_pekerjaan,
                'spesifikasi_kebutuhan' => $this->spesifikasi_kebutuhan ?: '',
                'tipe_kerja' => $this->tipe_kerja,
                'lokasi' => $this->lokasi,
                'application_deadline' => $this->application_deadline,
                'tampilkan_gaji' => $this->tampilkan_gaji ? true : false,
                'status' => 'Draft',
                'kuota' => $remainingQuota, // Mengunci kuota dari sisa kebutuhan MPP
            ]);

            session()->flash('message', 'Recruitment Request berhasil dibuat sebagai Draft.');
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
        $query = Mpp::whereIn('status', ['Approved', 'approved'])
            ->whereDoesntHave('recruitmentRequests', function ($query) {
                $query->where('status', '!=', 'Completed/Closed');
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
        ])->layout('layouts.app');
    }
}
