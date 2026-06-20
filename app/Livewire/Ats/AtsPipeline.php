<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\CandidateMovement;
use App\Models\Blacklist;
use App\Models\Scorecard;
use App\Models\InterviewSchedule;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;

/**
 * Class AtsPipeline
 *
 * Komponen Livewire untuk menampilkan halaman utama ATS (Dashboard Pipeline).
 * Menampilkan kandidat dalam bentuk tahapan (kanban-like board/list) dan
 * menyediakan aksi pindah tahapan, tolak (reject), terima (approve), dan blacklist.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsPipeline extends Component
{
    use WithPagination;

    // ==========================================
    // FILTER & STATUS PENCARIAN (Menyatu dengan URL)
    // ==========================================

    /** @var int|null ID Lowongan yang dipilih dari dropdown. */
    #[Url]
    public $selectedVacancyId = null;
    
    /** @var int|null ID Tahapan (Stage) yang sedang aktif dilihat. */
    #[Url]
    public $selectedStageId = null;
    
    /** @var string Kata kunci pencarian nama/email kandidat. */
    #[Url]
    public $search = '';

    // ==========================================
    // PROPERTI MODAL BLACKLIST
    // ==========================================

    /** @var bool Status tampil/sembunyikan modal blacklist. */
    public $showBlacklistModal = false;

    /** @var int|null ID Kandidat yang akan dimasukkan ke blacklist. */
    public $blacklistCandidateId = null;

    /** @var string Alasan mengapa kandidat ini diblacklist. */
    public $blacklistAlasan = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        return [
            'blacklistAlasan' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'blacklistAlasan.required' => 'Alasan blacklist wajib diisi.',
        'blacklistAlasan.min' => 'Alasan blacklist minimal 5 karakter.',
    ];

    /**
     * Inisialisasi awal saat komponen dimuat.
     * Mengatur lowongan aktif dan mengambil tahapan terakhir dari sesi (session).
     *
     * @param int|null $selectedVacancyId
     */
    public function mount($selectedVacancyId = null)
    {
        $this->selectedVacancyId = $selectedVacancyId;
        
        // Cek session untuk stage terakhir
        if (session()->has('pipeline_selected_stage')) {
            $this->selectedStageId = session()->get('pipeline_selected_stage');
        } else {
            // Default: atur stage terpilih ke stage urutan pertama
            $firstStage = Stage::getAllCached()->first();
            if ($firstStage) {
                $this->selectedStageId = $firstStage->id;
            }
        }
    }

    /**
     * Dijalankan otomatis ketika kotak pencarian diisi. Mereset paginasi.
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Dijalankan otomatis ketika opsi lowongan diganti. Mereset paginasi.
     */
    public function updatedSelectedVacancyId()
    {
        $this->resetPage();
    }

    /**
     * Dijalankan otomatis ketika tab tahapan (stage) diganti.
     * Menyimpan pilihan tahapan ke dalam sesi (session).
     */
    public function updatedSelectedStageId()
    {
        session()->put('pipeline_selected_stage', $this->selectedStageId);
        $this->resetPage();
    }

    /**
     * Memeriksa apakah kandidat telah memenuhi semua persyaratan pada tahapan saat ini
     * (misal: isi form nilai, jadwal wawancara) sebelum diizinkan pindah ke tahapan selanjutnya.
     *
     * @param \App\Models\Candidate $candidate
     * @return string|null Mengembalikan pesan error jika belum lengkap, atau null jika lengkap.
     */
    private function validateCurrentStageRequirements($candidate)
    {
        return app(\App\Services\CandidateService::class)->validateCurrentStageRequirements($candidate);
    }

    /**
     * Memindahkan kandidat ke tahapan (stage) lain.
     * Validasi kelengkapan data akan dilakukan terlebih dahulu.
     *
     * @param int $id ID Kandidat.
     * @param int $toStageId ID Tahapan tujuan.
     */
    public function moveCandidate($id, $toStageId)
    {
        $candidate = Candidate::findOrFail($id);
        $toStage = Stage::findOrFail($toStageId);

        // Check if attempting to move to same stage
        if ($candidate->current_stage_id == $toStageId) {
            return;
        }

        // Validate current stage scorecard/schedule requirements
        $validationError = $this->validateCurrentStageRequirements($candidate);
        if ($validationError) {
            session()->flash('error', $validationError);
            return;
        }

        \DB::transaction(function () use ($candidate, $toStage) {
            app(\App\Services\CandidateService::class)->moveCandidate($candidate, $toStage);
        });

        session()->flash('message', "Kandidat '{$candidate->name}' berhasil dipindahkan ke stage '{$toStage->name}'.");
    }

    /**
     * Menolak kandidat (Mengubah status menjadi REJECTED).
     *
     * @param int $id ID Kandidat yang ditolak.
     */
    public function reject($id)
    {
        $candidate = Candidate::findOrFail($id);
        
        $validationError = $this->validateCurrentStageRequirements($candidate);
        if ($validationError) {
            session()->flash('error', $validationError);
            return;
        }

        try {
            app(\App\Services\CandidateService::class)->rejectCandidate($candidate);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('message', "Kandidat '{$candidate->name}' berhasil ditolak.");
    }

    /**
     * Konfirmasi membuka modal untuk proses daftar hitam (blacklist).
     *
     * @param int $id ID Kandidat yang akan di-blacklist.
     */
    public function confirmBlacklist($id)
    {
        $this->resetValidation();
        $this->blacklistCandidateId = $id;
        $this->blacklistAlasan = '';
        $this->showBlacklistModal = true;
    }

    /**
     * Menyimpan data kandidat ke daftar hitam, lalu mengubah statusnya menjadi REJECTED.
     */
    public function blacklist()
    {
        $this->validate();

        $candidate = Candidate::findOrFail($this->blacklistCandidateId);

        try {
            app(\App\Services\CandidateService::class)->blacklistCandidate($candidate, $this->blacklistAlasan);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->showBlacklistModal = false;
        $this->blacklistCandidateId = null;
        $this->blacklistAlasan = '';

        session()->flash('message', "Kandidat '{$candidate->name}' berhasil dimasukkan ke daftar hitam (blacklist).");
    }

    /**
     * Menyetujui kandidat (Hired/Diterima).
     * Secara otomatis memindahkan kandidat ke tahap terakhir dan mengubah status menjadi OFFERED.
     * Kemudian mengarahkan HR ke halaman pengiriman surat penawaran (Offering).
     *
     * @param int $id ID Kandidat.
     */
    public function approve($id)
    {
        $candidate = Candidate::findOrFail($id);
        
        $validationError = $this->validateCurrentStageRequirements($candidate);
        if ($validationError) {
            session()->flash('error', $validationError);
            return;
        }

        try {
            app(\App\Services\CandidateService::class)->approveCandidate($candidate);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        return redirect()->route('ats.offering.send', ['candidateId' => $candidate->id]);
    }

    /**
     * Mengganti tahapan (tab stage) secara manual berdasarkan interaksi pengguna.
     *
     * @param int $stageId
     */
    public function selectStage($stageId)
    {
        $this->selectedStageId = $stageId;
        session()->put('pipeline_selected_stage', $stageId);
        $this->resetPage();
    }

    /**
     * Render komponen antarmuka Pipeline.
     * Memuat lowongan, tahapan, jumlah kandidat per tahapan, dan daftar kandidat.
     */
    public function render()
    {
        // 1. Ambil daftar lowongan yang telah diterbitkan atau siap diterbitkan
        $vacancies = Vacancy::whereIn('status', ['Published', 'Ready to Publish'])->get();

        // 2. Ambil semua definisi tahapan rekrutmen
        $stages = Stage::getAllCached();

        // 3. Hitung jumlah kandidat per tahapan berdasarkan filter pencarian
        $stageCounts = app(\App\Repositories\CandidateRepository::class)->getStageCounts($this->selectedVacancyId, $this->search);

        // 4. Ambil data kandidat di tahapan yang sedang aktif dengan batas paginasi 10 data
        $candidates = app(\App\Repositories\CandidateRepository::class)->getPipelineCandidates($this->selectedVacancyId, $this->selectedStageId, $this->search, 10);

        return view('livewire.ats.ats-pipeline', [
            'vacancies' => $vacancies,
            'stages' => $stages,
            'stageCounts' => $stageCounts,
            'candidates' => $candidates,
        ]);
    }
}
