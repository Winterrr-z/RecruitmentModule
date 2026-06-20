<?php

namespace App\Livewire\Ats;

use App\Models\Blacklist;
use App\Models\Candidate;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

/**
 * Class AtsBlacklist
 *
 * Komponen Livewire untuk mengelola daftar hitam (blacklist) kandidat.
 * Kandidat yang dimasukkan ke sini akan ditolak secara otomatis 
 * pada lamaran-lamaran berikutnya.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsBlacklist extends Component
{
    use WithPagination;

    // ==========================================
    // PROPERTI FILTER & PENCARIAN
    // ==========================================
    
    /** @var string Kata kunci pencarian nama/email/telepon kandidat di tabel. */
    public $search = '';

    // ==========================================
    // PROPERTI MODAL & FORM
    // ==========================================
    
    /** @var bool Status tampil/sembunyikan modal tambah blacklist. */
    public $showModal = false;

    /** @var bool Status tampil/sembunyikan daftar pilihan otomatis kandidat. */
    public $showCandidatePicker = false;

    /** @var string Kata kunci untuk mencari kandidat yang sudah ada di database saat mengisi form. */
    public $candidateSearch = '';

    /** @var string Nama lengkap yang akan di-blacklist. */
    public $name = '';

    /** @var string Email yang akan di-blacklist. */
    public $email = '';

    /** @var string Nomor telepon yang akan di-blacklist. */
    public $phone = '';

    /** @var string Alasan mengapa kandidat ini di-blacklist. */
    public $reason = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'reason' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.max' => 'Nama lengkap maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'phone.required' => 'Nomor telepon wajib diisi.',
        'phone.max' => 'Nomor telepon maksimal 20 karakter.',
        'reason.required' => 'Alasan wajib diisi.',
        'reason.min' => 'Alasan minimal 5 karakter.',
    ];

    /**
     * Dijalankan otomatis ketika ada ketikan di kolom pencarian tabel utama.
     * Mereset paginasi kembali ke halaman pertama.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Membuka modal form tambah blacklist baru.
     * Menghapus pesan error dan form isian sebelumnya.
     */
    public function openAddModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * Menampilkan atau menyembunyikan kotak pencarian kandidat di dalam form modal.
     */
    public function toggleCandidatePicker()
    {
        $this->showCandidatePicker = !$this->showCandidatePicker;
        $this->candidateSearch = '';
    }

    /**
     * Mengisi form secara otomatis berdasarkan data kandidat yang dipilih dari daftar pencarian.
     *
     * @param int $candidateId ID kandidat yang dipilih.
     */
    public function selectCandidate($candidateId)
    {
        $candidate = Candidate::findOrFail($candidateId);
        $this->name = $candidate->name;
        $this->email = $candidate->email;
        $this->phone = $candidate->phone;
        
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    /**
     * Menyimpan data blacklist baru ke dalam database.
     * Menggunakan CandidateService agar logika blacklist tersentralisasi.
     */
    public function save()
    {
        $this->validate();

        app(\App\Services\CandidateService::class)->blacklistDetails(
            $this->name,
            $this->email,
            $this->phone,
            $this->reason
        );

        $this->showModal = false;
        $this->resetForm();

        session()->flash('message', "Data blacklist baru untuk '{$this->name}' berhasil ditambahkan.");
    }

    /**
     * Menghapus data dari daftar blacklist.
     * Akan mengubah status kandidat terkait dari 'Blacklisted' kembali menjadi 'Rejected'.
     * Menggunakan transaksi database agar aman.
     *
     * @param int $id ID dari data blacklist yang akan dihapus.
     */
    public function deleteBlacklist($id)
    {
        $blacklist = Blacklist::findOrFail($id);
        
        \DB::transaction(function () use ($blacklist) {
            $candidates = Candidate::where(fn($q) => $q->where('email', $blacklist->email)->orWhere('phone', $blacklist->phone))
                ->where('status', \App\Enums\CandidateStatus::BLACKLISTED)
                ->get();

            foreach ($candidates as $c) {
                $c->status = \App\Enums\CandidateStatus::REJECTED;
                $c->save();
            }

            $blacklist->delete();
        });

        session()->flash('message', "Data blacklist '{$blacklist->name}' berhasil dihapus.");
    }

    /**
     * Mengosongkan seluruh isian pada form modal.
     */
    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->reason = '';
        $this->showCandidatePicker = false;
        $this->candidateSearch = '';
    }

    /**
     * Mencari dan mengembalikan daftar kandidat dari database 
     * untuk fitur auto-complete di dalam form tambah blacklist.
     *
     * @return \Illuminate\Database\Eloquent\Collection Daftar kandidat.
     */
    public function getCandidates()
    {
        if (strlen($this->candidateSearch) < 2) {
            return collect();
        }

        return Candidate::where(fn($q) => $q->where('name', 'like', '%' . $this->candidateSearch . '%')
            ->orWhere('email', 'like', '%' . $this->candidateSearch . '%'))
            ->limit(5)
            ->get();
    }

    /**
     * Render komponen antarmuka tabel blacklist beserta form modalnya.
     */
    public function render()
    {
        $query = Blacklist::query();

        if ($this->search) {
            $query->where(fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%'));
        }

        $blacklistList = $query->orderBy('created_at', 'desc')->paginate(10);
        $pickerCandidates = $this->getCandidates();

        return view('livewire.ats.blacklist', [
            'blacklistList' => $blacklistList,
            'pickerCandidates' => $pickerCandidates,
        ]);
    }
}
