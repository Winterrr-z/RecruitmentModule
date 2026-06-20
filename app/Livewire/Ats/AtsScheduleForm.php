<?php

namespace App\Livewire\Ats;

use App\Models\Candidate;
use App\Models\Stage;
use App\Models\InterviewSchedule;
use Livewire\Component;
use Livewire\Attributes\Layout;

/**
 * Class AtsScheduleForm
 *
 * Komponen Livewire untuk mengatur jadwal wawancara (Interview Schedule) kandidat 
 * pada tahapan tertentu. Mendukung penjadwalan luring (venue) dan daring (virtual link).
 *
 * @package App\Livewire\Ats
 */
#[Layout('layouts.hr')]
class AtsScheduleForm extends Component
{
    /** @var int ID kandidat yang akan dijadwalkan. */
    public $candidateId;

    /** @var int ID tahapan tempat jadwal ini berlaku. */
    public $stageId;

    /** @var \App\Models\Candidate Objek kandidat terkait. */
    public $candidate;

    /** @var \App\Models\Stage Objek tahapan (stage) terkait. */
    public $stage;

    // ==========================================
    // ISIAN FORMULIR JADWAL
    // ==========================================

    /** @var string Tanggal wawancara (Y-m-d). */
    public $date;

    /** @var string Waktu wawancara (H:i). */
    public $time;

    /** @var string|null Lokasi tatap muka wawancara. */
    public $venue;

    /** @var string|null Tautan ruang virtual meeting (opsional). */
    public $virtual_link;

    protected function rules()
    {
        return [
            'date' => 'required|date',
            'time' => 'required',
            'venue' => 'nullable|string|max:200',
            'virtual_link' => 'nullable|url|max:200',
        ];
    }

    protected $messages = [
        'date.required' => 'Tanggal interview wajib dipilih.',
        'date.date' => 'Tanggal format salah.',
        'time.required' => 'Waktu interview wajib diisi.',
        'virtual_link.url' => 'Format tautan virtual meeting tidak valid.',
    ];

    /**
     * Memuat data awal formulir jadwal.
     * Jika jadwal sudah pernah dibuat, maka nilai formulir akan diisi dengan data lama.
     * Jika belum, lokasi dan tautan virtual akan diisi dengan nilai standar (default) dari tahapan.
     *
     * @param int $candidateId
     * @param int $stageId
     */
    public function mount($candidateId, $stageId)
    {
        $this->candidateId = $candidateId;
        $this->stageId = $stageId;
        $this->candidate = Candidate::findOrFail($candidateId);
        $this->stage = Stage::findOrFail($stageId);

        // Muat jadwal jika sudah pernah disimpan
        $existing = InterviewSchedule::where('candidate_id', $candidateId)
            ->where('stage_id', $stageId)
            ->first();

        if ($existing) {
            $this->date = $existing->date ? $existing->date->format('Y-m-d') : null;
            $this->time = $existing->time;
            $this->venue = $existing->venue;
            $this->virtual_link = $existing->virtual_link;
        } else {
            $this->venue = $this->stage->default_location;
            $this->virtual_link = $this->stage->default_virtual_link;
        }
    }

    /**
     * Menyimpan atau memperbarui data jadwal ke database.
     */
    public function save()
    {
        $this->validate();

        $schedule = InterviewSchedule::updateOrCreate(
            [
                'candidate_id' => $this->candidateId,
                'stage_id' => $this->stageId,
            ],
            [
                'date' => $this->date,
                'time' => $this->time,
                'venue' => $this->venue,
                'virtual_link' => $this->virtual_link,
            ]
        );

        // Jika wawancara dijadwalkan untuk hari ini, buat notifikasi untuk seluruh HR
        if (\Carbon\Carbon::parse($this->date)->isToday()) {
            $hrUsers = \App\Models\User::where('role', 'hr')->get();
            foreach ($hrUsers as $hr) {
                $exists = \App\Models\Notification::where('user_id', $hr->id)
                    ->where('interview_schedule_id', $schedule->id)
                    ->whereDate('created_at', \Carbon\Carbon::today())
                    ->exists();

                if (!$exists) {
                    \App\Models\Notification::create([
                        'user_id'               => $hr->id,
                        'type'                  => 'interview',
                        'title'                 => 'Interview Hari Ini',
                        'message'               => $this->candidate->name . ' - ' . $this->stage->name . ' (' . $this->time . ')',
                        'icon'                  => 'calendar_today',
                        'candidate_id'          => $this->candidateId,
                        'interview_schedule_id' => $schedule->id,
                    ]);
                }
            }
        }

        session()->flash('message', 'Jadwal interview berhasil disimpan.');

        return redirect()->route('ats.candidate.detail', ['candidateId' => $this->candidateId]);
    }

    /**
     * Render komponen antarmuka formulir jadwal wawancara.
     */
    public function render()
    {
        return view('livewire.ats.schedule-form');
    }
}
