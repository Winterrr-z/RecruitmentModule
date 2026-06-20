<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Mail\OfferingLetterMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class OfferingSend
 *
 * Komponen Livewire untuk memeriksa kelayakan dan mengirimkan Offering Letter 
 * melalui email kepada kandidat yang lolos ke tahap 'Final'.
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class OfferingSend extends Component
{
    /** @var int ID Kandidat penerima penawaran. */
    public $candidateId;

    /** @var \App\Models\Candidate Objek kandidat terkait. */
    public $candidate;

    /** @var \App\Models\Vacancy Objek lowongan terkait. */
    public $vacancy;

    /** @var string URL tujuan untuk tombol kembali. */
    public $backUrl;

    /** @var string Label teks untuk tombol kembali. */
    public $backLabel;

    /** @var bool Menandakan apakah kandidat memenuhi syarat untuk menerima surat penawaran. */
    public $isValid = false;

    /** @var string Pesan error jika kondisi kelayakan ($isValid) tidak terpenuhi. */
    public $errorMessage = '';

    /**
     * Inisialisasi awal. Memuat data kandidat, lowongan, dan mengecek kelayakan awal.
     *
     * @param int $candidateId
     */
    public function mount($candidateId)
    {
        $referer = request()->headers->get('referer');
        $from = request()->query('from');

        if ($from === 'candidates' || ($referer && str_contains($referer, '/ats/candidates'))) {
            $this->backUrl = route('ats.candidates');
            $this->backLabel = 'All Candidates';
        } else {
            $this->backUrl = route('ats.dashboard');
            $this->backLabel = 'Pipeline';
        }

        $this->candidateId = $candidateId;
        $this->candidate = Candidate::with(['vacancy', 'currentStage'])->findOrFail($candidateId);
        $this->vacancy = $this->candidate->vacancy;

        $this->validateCandidate();
    }

    /**
     * Melakukan pengecekan kelayakan sebelum mengirim surat penawaran:
     * 1. Harus berada di tahapan 'Final'.
     * 2. Status belum rejected/blacklisted/expired.
     * 3. Ada data lowongan.
     * 4. Lowongan masih memiliki kuota kosong.
     */
    protected function validateCandidate()
    {
        // 1. Stage check (name: 'Final' or id: 2)
        $stage = $this->candidate->currentStage;
        if (!$stage || !$stage->is_final_stage) {
            $this->isValid = false;
            $this->errorMessage = 'Kandidat harus berada di stage "Final" untuk dikirimi offering letter.';
            return;
        }

        // 2. Status check (must be \App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, or \App\Enums\CandidateStatus::OFFERED)
        if (!in_array($this->candidate->status, [\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::OFFERED])) {
            $this->isValid = false;
            $this->errorMessage = 'Status kandidat tidak valid untuk dikirimi offering letter. Saat ini status kandidat adalah "' . $this->candidate->status->value . '".';
            return;
        }

        // 3. Vacancy check
        if (!$this->vacancy) {
            $this->isValid = false;
            $this->errorMessage = 'Data vacancy untuk kandidat ini tidak ditemukan.';
            return;
        }

        // 4. Quota check
        if ($this->vacancy->quota <= 0) {
            $this->isValid = false;
            $this->errorMessage = 'Kuota vacancy untuk jabatan "' . $this->vacancy->job_title . '" sudah habis.';
            return;
        }

        $this->isValid = true;
    }

    /**
     * Menghasilkan token, menyimpan ke database (status OFFERED),
     * lalu mengirimkan email Offering Letter ke kandidat.
     */
    public function sendOffering()
    {
        $this->validateCandidate();

        if (!$this->isValid) {
            session()->flash('error', $this->errorMessage);
            return;
        }

        $token = hash_hmac('sha256', $this->candidate->id . now()->timestamp . Str::random(40), config('app.key'));
        $expiresAt = now()->addDays(3);

        DB::transaction(function () use ($token, $expiresAt) {
            $this->candidate->update([
                'status' => \App\Enums\CandidateStatus::OFFERED,
                'offering_token' => $token,
                'offering_token_expires_at' => $expiresAt,
            ]);
        });

        // Send email
        try {
            Mail::to($this->candidate->email)->send(new OfferingLetterMail($this->candidate, $this->vacancy, $token, $expiresAt));
            session()->flash('message', 'Offering letter telah dikirim ke email kandidat.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email offering letter untuk kandidat {$this->candidate->id}: " . $e->getMessage());
            session()->flash('error', 'Kandidat berhasil di-update menjadi Offered, namun email gagal terkirim: ' . $e->getMessage());
        }

        return redirect()->route('ats.dashboard');
    }

    /**
     * Render komponen antarmuka pengiriman offering.
     */
    public function render()
    {
        return view('livewire.ats.offering-send');
    }
}
