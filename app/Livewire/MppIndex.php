<?php

namespace App\Livewire;

use App\Models\Mpp;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Class MppIndex
 * 
 * Komponen Livewire untuk menampilkan daftar Manpower Planning (MPP).
 * Menangani fungsi CRUD termasuk pembuatan, pengeditan (melalui modal),
 * penghapusan, dan pemformatan data seperti gaji dan kalkulasi target waktu.
 *
 * @package App\Livewire
 */
class MppIndex extends Component
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection Daftar semua MPP.
     */
    public $mpps;

    /**
     * @var bool Status tampilnya modal (Create/Edit).
     */
    public $showModal = false;

    /**
     * @var bool Penanda apakah modal saat ini dalam mode Edit.
     */
    public $isEdit = false;

    /**
     * @var int|null ID MPP yang sedang diedit (null jika mode Create).
     */
    public $mppId = null;

    // Form fields
    /** @var string|null Nama perencanaan (Plan) MPP. */
    public $nama_plan;
    
    /** @var string|null Departemen yang dituju. */
    public $departemen;
    
    /** @var string|null Jabatan yang dibutuhkan. */
    public $jabatan;
    
    /** @var int Jumlah kebutuhan tenaga kerja (default 1). */
    public $jumlah_kebutuhan = 1;
    
    /** @var string|int|null Estimasi gaji minimum (diformat saat diinput). */
    public $estimasi_gaji_min;
    
    /** @var string|int|null Estimasi gaji maksimum (diformat saat diinput). */
    public $estimasi_gaji_max;
    
    /** @var int|null SLA (Service Level Agreement) dalam jumlah bulan. */
    public $sla_bulan;
    
    /** @var string|null Tanggal target waktu absolut berdasarkan SLA. */
    public $target_waktu_absolut;
    
    /** @var string|null Catatan tambahan. */
    public $note;

    /**
     * Component mount lifecycle hook.
     * Mengecek query string 'edit_id' untuk membuka modal edit secara otomatis.
     * 
     * @return void
     */
    public function mount()
    {
        $editId = request()->query('edit_id');
        if ($editId) {
            $this->openEditModal($editId);
        }
    }

    /**
     * Hook that fires when sla_bulan is updated.
     * Memicu perhitungan ulang target waktu absolut.
     * 
     * @return void
     */
    public function updatedSlaBulan()
    {
        $this->calculateTargetWaktu();
    }

    /**
     * Hook that fires when estimasi_gaji_min is updated.
     * Memformat input menjadi format angka dengan pemisah ribuan.
     * 
     * @param string|int $value
     * @return void
     */
    public function updatedEstimasiGajiMin($value)
    {
        $this->estimasi_gaji_min = $this->formatNumber($value);
    }

    /**
     * Hook that fires when estimasi_gaji_max is updated.
     * Memformat input menjadi format angka dengan pemisah ribuan.
     * 
     * @param string|int $value
     * @return void
     */
    public function updatedEstimasiGajiMax($value)
    {
        $this->estimasi_gaji_max = $this->formatNumber($value);
    }

    /**
     * Format a string value with comma as thousand separator.
     * Membersihkan karakter non-digit dan mengembalikan format angka ribuan.
     * 
     * @param mixed $value
     * @return string|null
     */
    protected function formatNumber($value)
    {
        if (empty($value)) return null;
        $clean = preg_replace('/\D/', '', $value);
        return $clean !== '' ? number_format((int)$clean, 0, '.', ',') : null;
    }

    /**
     * Strip commas from a formatted salary input and return integer or null.
     * Membersihkan pemisah ribuan untuk validasi dan penyimpanan ke database.
     * 
     * @param mixed $value
     * @return int|null
     */
    protected function getNumericSalary($value)
    {
        if (empty($value)) return null;
        $clean = preg_replace('/\D/', '', $value);
        return $clean !== '' ? (int)$clean : null;
    }

    /**
     * Calculate target date absolute.
     * Menambahkan jumlah SLA (bulan) ke tanggal saat ini.
     * 
     * @return void
     */
    protected function calculateTargetWaktu()
    {
        if (is_numeric($this->sla_bulan) && $this->sla_bulan > 0) {
            $this->target_waktu_absolut = now()->addMonths((int)$this->sla_bulan)->format('Y-m-d');
        } else {
            $this->target_waktu_absolut = null;
        }
    }

    /**
     * Open create modal.
     * Me-reset form dan menampilkan modal untuk membuat MPP baru.
     * 
     * @return void
     */
    public function openCreateModal()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->isEdit = false;
        $this->mppId = null;
        $this->showModal = true;
    }

    /**
     * Open edit modal.
     * Memuat data MPP berdasarkan ID ke dalam form dan menampilkan modal.
     * 
     * @param int $id
     * @return void
     */
    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetForm();
        
        $this->mppId = $id;
        $this->isEdit = true;
        
        $mpp = Mpp::findOrFail($id);
        $this->nama_plan = $mpp->nama_plan;
        $this->departemen = $mpp->departemen;
        $this->jabatan = $mpp->jabatan;
        $this->jumlah_kebutuhan = $mpp->jumlah_kebutuhan;
        
        // Format integers from DB with commas
        $this->estimasi_gaji_min = $mpp->estimasi_gaji_min ? number_format($mpp->estimasi_gaji_min, 0, '.', ',') : null;
        $this->estimasi_gaji_max = $mpp->estimasi_gaji_max ? number_format($mpp->estimasi_gaji_max, 0, '.', ',') : null;
        
        $this->sla_bulan = $mpp->sla_bulan;
        $this->target_waktu_absolut = $mpp->target_waktu_absolut ? $mpp->target_waktu_absolut->format('Y-m-d') : null;
        $this->note = $mpp->note;
        
        $this->showModal = true;
    }

    /**
     * Close modal.
     * Menutup modal dan me-reset isi form.
     * 
     * @return void
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Reset form fields.
     * Mengembalikan semua properti form ke nilai default (kosong).
     * 
     * @return void
     */
    protected function resetForm()
    {
        $this->nama_plan = null;
        $this->departemen = null;
        $this->jabatan = null;
        $this->jumlah_kebutuhan = 1;
        $this->estimasi_gaji_min = null;
        $this->estimasi_gaji_max = null;
        $this->sla_bulan = null;
        $this->target_waktu_absolut = null;
        $this->note = null;
    }

    /**
     * Save the form data.
     * Memvalidasi input, menyimpan (Create/Update) data MPP ke database, dan menampilkan pesan flash.
     * 
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
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
                'sla_bulan' => 'required|integer|min:1',
                'note' => 'nullable|string|max:1000',
            ], [
                'nama_plan.required' => 'Nama Plan wajib diisi.',
                'departemen.required' => 'Departemen wajib diisi.',
                'jabatan.required' => 'Jabatan wajib diisi.',
                'jumlah_kebutuhan.required' => 'Jumlah Kebutuhan wajib diisi.',
                'jumlah_kebutuhan.min' => 'Jumlah Kebutuhan minimal 1 Orang.',
                'estimasi_gaji_max.gt' => 'Estimasi Gaji Max harus lebih besar dari Gaji Min.',
                'sla_bulan.required' => 'SLA wajib diisi.',
                'sla_bulan.min' => 'SLA minimal 1 bulan.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Restore formatted copies so the input field continues showing formatted value
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
            'sla_bulan' => $this->sla_bulan,
            'target_waktu_absolut' => $this->target_waktu_absolut,
            'note' => $this->note ?: null,
            // default fallback schema fields
            'syarat_pendidikan' => 'Minimal D3',
            'syarat_pengalaman' => 'Minimal 1 Tahun',
            'keahlian' => [],
        ];

        if ($this->isEdit) {
            $mpp = Mpp::findOrFail($this->mppId);
            $mpp->update($data);
            session()->flash('message', 'Manpower Plan berhasil diperbarui.');
        } else {
            Mpp::create($data);
            session()->flash('message', 'Manpower Plan berhasil dibuat.');
        }

        $this->closeModal();
    }

    /**
     * Delete the specified Manpower Plan.
     * Menghapus MPP berdasarkan ID dari database.
     * 
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $mpp = Mpp::findOrFail($id);
        $mpp->delete();

        session()->flash('message', 'Manpower Plan berhasil dihapus.');
    }

    /**
     * Render the Livewire component.
     * Memuat daftar semua MPP dari database dan menampilkannya di view index.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $this->mpps = Mpp::latest()->get();

        return view('livewire.mpp.index')
            ->layout('layouts.app');
    }
}
