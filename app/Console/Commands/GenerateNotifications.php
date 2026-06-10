<?php

namespace App\Console\Commands;

use App\Models\Candidate;
use App\Models\InterviewSchedule;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate';

    protected $description = 'Generate notifications untuk aplikasi candidate dan interview hari ini';

    public function handle()
    {
        $today = Carbon::today();
        $hrUsers = User::where('role', 'hr')->get();

        foreach ($hrUsers as $user) {
            // Hapus notifikasi hari ini yang sudah ada untuk user ini (avoid duplicate)
            Notification::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->delete();

            // 1. Generate notifikasi untuk candidates yang apply hari ini
            $todayApplications = Candidate::where('status', \App\Enums\CandidateStatus::APPLIED)
                ->whereDate('created_at', $today)
                ->get();

            if ($todayApplications->count() > 3) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'applications_bulk',
                    'title' => 'Aplikasi Kandidat Baru',
                    'message' => $todayApplications->count() . ' kandidat baru apply hari ini',
                    'icon' => 'people',
                ]);
            } else {
                foreach ($todayApplications as $candidate) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'application_single',
                        'title' => 'Aplikasi Kandidat Baru',
                        'message' => $candidate->name . ' apply untuk posisi ' . ($candidate->vacancy?->job_title ?? 'N/A'),
                        'icon' => 'person_add',
                        'candidate_id' => $candidate->id,
                    ]);
                }
            }

            // 2. Generate notifikasi untuk interviews hari ini
            $todayInterviews = InterviewSchedule::whereDate('date', $today)
                ->with('candidate', 'stage')
                ->get();

            foreach ($todayInterviews as $interview) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'interview',
                    'title' => 'Interview Hari Ini',
                    'message' => $interview->candidate->name . ' - ' . $interview->stage->name . ' (' . $interview->time . ')',
                    'icon' => 'calendar_today',
                    'candidate_id' => $interview->candidate_id,
                    'interview_schedule_id' => $interview->id,
                ]);
            }
        }

        $this->info('Notifikasi berhasil di-generate untuk semua HR users');
    }
}
