<?php

namespace Tests\Feature;

use App\Models\Vacancy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicJobListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: buat vacancy dengan data valid sesuai CHECK constraint DB.
     * employment_type: full-time | contract
     * location: remote | on-site
     */
    private function makeVacancy(array $overrides = []): Vacancy
    {
        $employmentType = $overrides['employment_type'] ?? 'full-time';
        $location       = $overrides['location'] ?? 'on-site';

        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP ' . uniqid(),
            'department'           => $overrides['department'] ?? 'IT',
            'job_title'            => $overrides['job_title'] ?? 'Posisi',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => $overrides['job_title'] ?? 'Posisi',
            'department'           => $overrides['department'] ?? 'IT',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => $employmentType,
            'location'             => $location,
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        return Vacancy::create(array_merge([
            'rr_id'                => $rr->id,
            'job_title'            => 'Posisi Default',
            'department'           => 'IT',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => $employmentType,
            'location'             => $location,
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ], $overrides));
    }

    /** Halaman career dapat diakses tanpa login (public route) */
    public function test_careers_page_is_publicly_accessible(): void
    {
        $this->get(route('careers'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Cw\PublicJobList::class);
    }

    /** Hanya vacancy Published dengan quota > 0 dan belum expired yang muncul */
    public function test_only_published_active_vacancies_are_shown(): void
    {
        // Vacancy aktif
        $this->makeVacancy(['job_title' => 'Backend Engineer', 'status' => 'Published', 'quota' => 1]);

        // Vacancy tidak aktif — status Draft
        $this->makeVacancy(['job_title' => 'Frontend Engineer', 'status' => 'Draft', 'quota' => 1]);

        // Vacancy tidak aktif — quota habis
        $this->makeVacancy(['job_title' => 'DevOps Engineer', 'status' => 'Published', 'quota' => 0]);

        // Vacancy tidak aktif — expired
        $this->makeVacancy([
            'job_title'            => 'QA Engineer',
            'status'               => 'Published',
            'quota'                => 1,
            'application_deadline' => now()->subDay()->format('Y-m-d'),
        ]);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->assertSee('Backend Engineer')
            ->assertDontSee('Frontend Engineer')
            ->assertDontSee('DevOps Engineer')
            ->assertDontSee('QA Engineer');
    }

    /** Search berdasarkan job_title berhasil memfilter */
    public function test_search_by_job_title_filters_results(): void
    {
        $this->makeVacancy(['job_title' => 'Data Scientist']);
        $this->makeVacancy(['job_title' => 'Network Engineer']);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('search', 'Data Scientist')
            ->assertSee('Data Scientist')
            ->assertDontSee('Network Engineer');
    }

    /** Search berdasarkan department berhasil memfilter */
    public function test_search_by_department_filters_results(): void
    {
        $this->makeVacancy(['job_title' => 'Legal Counsel',   'department' => 'Legal']);
        $this->makeVacancy(['job_title' => 'Finance Analyst', 'department' => 'Finance']);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('search', 'Legal')
            ->assertSee('Legal Counsel')
            ->assertDontSee('Finance Analyst');
    }

    /**
     * Filter berdasarkan employment_type berhasil.
     * employment_type valid: full-time | contract
     */
    public function test_filter_by_employment_type(): void
    {
        $this->makeVacancy(['job_title' => 'Full Time Dev',  'employment_type' => 'full-time']);
        $this->makeVacancy(['job_title' => 'Contract Admin', 'employment_type' => 'contract']);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('selectedTipeKerja', 'full-time')
            ->assertSee('Full Time Dev')
            ->assertDontSee('Contract Admin');
    }

    /** Filter berdasarkan location berhasil (valid: remote | on-site) */
    public function test_filter_by_location(): void
    {
        $this->makeVacancy(['job_title' => 'Remote Worker', 'location' => 'remote']);
        $this->makeVacancy(['job_title' => 'Office Worker', 'location' => 'on-site']);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('selectedLokasi', 'remote')
            ->assertSee('Remote Worker')
            ->assertDontSee('Office Worker');
    }

    /** resetFilters() menghapus semua filter aktif */
    public function test_reset_filters_clears_all_active_filters(): void
    {
        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('search', 'Engineer')
            ->set('selectedTipeKerja', 'full-time')
            ->set('selectedLokasi', 'remote')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('selectedTipeKerja', '')
            ->assertSet('selectedLokasi', '');
    }

    /** Gabungan filter search + employment_type berhasil */
    public function test_combined_search_and_type_filter(): void
    {
        $this->makeVacancy(['job_title' => 'Cloud Architect', 'employment_type' => 'full-time', 'location' => 'remote']);
        $this->makeVacancy(['job_title' => 'Cloud Support',   'employment_type' => 'contract',  'location' => 'remote']);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('search', 'Cloud')
            ->set('selectedTipeKerja', 'full-time')
            ->assertSee('Cloud Architect')
            ->assertDontSee('Cloud Support');
    }
}
