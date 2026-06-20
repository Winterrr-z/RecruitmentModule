<?php

namespace App\Services;

use App\Models\Rr;
use App\Enums\RrStatus;
use App\Enums\VacancyStatus;
use Illuminate\Support\Facades\DB;

class RrService
{
    /**
     * Publish RR (ubah status ke 'Published').
     * Otomatis membuat/update Vacancy untuk publik.
     *
     * @param Rr $rr
     * @return void
     */
    public function publish(Rr $rr): void
    {
        DB::transaction(function () use ($rr) {
            $rr->update(['status' => RrStatus::PUBLISHED]);

            // Buat atau update Vacancy otomatis
            $rr->vacancy()->updateOrCreate(
                ['rr_id' => $rr->id],
                [
                    'title' => $rr->title,
                    'quota' => $rr->quota,
                    'job_title' => $rr->job_title,
                    'department' => $rr->department,
                    'employment_type' => $rr->employment_type,
                    'location' => $rr->location,
                    'application_deadline' => $rr->application_deadline,
                    'show_salary' => $rr->show_salary,
                    'estimated_salary_min' => $rr->estimated_salary_min,
                    'estimated_salary_max' => $rr->estimated_salary_max,
                    'job_description' => $rr->job_description,
                    'job_requirements' => $rr->job_requirements,
                    'status' => VacancyStatus::PUBLISHED,
                ]
            );
        });
    }

    /**
     * Nonaktifkan RR (ubah status dari 'Published' ke 'Ready to Publish').
     *
     * @param Rr $rr
     * @return void
     */
    public function unpublish(Rr $rr): void
    {
        DB::transaction(function () use ($rr) {
            $rr->update(['status' => RrStatus::READY_TO_PUBLISH]);

            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => VacancyStatus::DRAFT]);
            }
        });
    }

    /**
     * Tutup RR (ubah status ke 'Closed').
     *
     * @param Rr $rr
     * @return void
     */
    public function close(Rr $rr): void
    {
        DB::transaction(function () use ($rr) {
            $rr->update(['status' => RrStatus::CLOSED]);

            if ($rr->vacancy) {
                $rr->vacancy->update(['status' => VacancyStatus::CLOSED]);
            }
        });
    }

    /**
     * Hapus RR draft jika tidak memiliki pelamar.
     *
     * @param Rr $rr
     * @throws \Exception
     * @return void
     */
    public function delete(Rr $rr): void
    {
        if ($rr->hiredCount() > 0 || $rr->status !== RrStatus::READY_TO_PUBLISH) {
            throw new \Exception('Recruitment Request yang memiliki pelamar Hired atau statusnya bukan Ready to Publish tidak dapat dihapus.');
        }

        DB::transaction(function () use ($rr) {
            $rr->delete();
        });
    }
}
