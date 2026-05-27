<?php

namespace App\Livewire;

use App\Models\Mpp;
use App\Models\Lowongan;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class RRForm
 * 
 * Komponen Livewire untuk form pembuatan Recruitment Request (RR) Baru.
 * Menangani pemilihan MPP, field read-only, validasi kuota sisa, dan penyimpanan ke database.
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
     * @return void|\Illuminate\Http\RedirectResponse
     */
    public function mount($mppId = null)
    {
        // Fallback mengambil mppId dari query string jika tidak disediakan lewat parameter route
        if (!$mppId) {
            $mppId = request()->query('mpp_id');
        }

        if ($mppId) {
            $mpp = Mpp::findOrFail($mppId);

            // Validasi status approved
            if (strtolower($mpp->status) !== 'approved') {
                session()->flash('error', 'Hanya Manpower Planning yang telah disetujui (Approved) yang dapat dibuatkan lowongan.');
                return redirect()->route('rr.index');
            }

            // Validasi anti-duplikasi: satu MPP hanya boleh memiliki maksimal 1 lowongan (RR)
            $exists = Lowongan::where('mpp_id', $mpp->id)->exists();
            if ($exists) {
                session()->flash('error', 'Manpower Planning ini sudah memiliki lowongan pekerjaan yang terdaftar.');
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
        $this->kuota = $mpp->jumlah_kebutuhan; // Kuota RR otomatis terisi dari jumlah kebutuhan MPP
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
            $this->populateMppFields($mpp);
        } else {
            $this->clearMppFields();
        }
    }

    /**
     * Simpan data lowongan ke database sebagai Draft.
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

        // Validasi status approved
        if (strtolower($mpp->status) !== 'approved') {
            session()->flash('error', 'Hanya Manpower Planning yang telah disetujui (Approved) yang dapat dibuatkan lowongan.');
            return redirect()->route('rr.index');
        }

        // Validasi anti-duplikasi sebelum submit
        $exists = Lowongan::where('mpp_id', $this->selectedMppId)->exists();
        if ($exists) {
            session()->flash('error', 'Manpower Planning ini sudah memiliki lowongan pekerjaan yang terdaftar.');
            return redirect()->route('rr.index');
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

        // Simpan Lowongan baru sebagai Ready to Publish (Draft)
        Lowongan::create([
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
            'status' => 'Ready to Publish',
            'kuota' => $mpp->jumlah_kebutuhan, // Mengunci kuota dari MPP
        ]);

        session()->flash('message', 'Recruitment Request berhasil dibuat sebagai Draft.');

        return redirect()->route('rr.index');
    }

    /**
     * Render komponen Livewire.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Ambil semua MPP Approved yang BELUM memiliki lowongan sama sekali
        $mppsDropdown = Mpp::whereIn('status', ['Approved', 'approved'])
            ->whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('lowongans')
                    ->whereColumn('lowongans.mpp_id', 'mpps.id');
            })
            ->get();

        return view('livewire.rr-form', [
            'mppsDropdown' => $mppsDropdown
        ])->layout('layouts.app');
    }
}
