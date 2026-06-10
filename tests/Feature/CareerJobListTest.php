<?php

namespace Tests\Feature;

use App\Models\Mpp;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class CareerJobListTest extends TestCase
{
    use RefreshDatabase;

    private $mpp;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat dummy MPP untuk relasi Vacancy
        $this->mpp = Mpp::create([
            'plan_name' => 'Plan Karir',
            'department' => 'Teknologi',
            'job_title' => 'Developer',
            'quota' => 5,
            'sla_days' => 90,
            'absolute_target_date' => now()->addDays(90)->format('Y-m-d'),
        ]);
    }

    public function test_careers_page_contains_livewire_component()
    {
        $this->get(route('careers'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Cw\PublicJobList::class);
    }

    public function test_displays_only_published_active_jobs_with_quota_and_valid_deadline()
    {
        // 1. Valid Vacancy (Published, quota > 0, deadline in future)
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $validVacancy = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Laravel Developer',
            'department' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Kerja Laravel 12',
            'job_requirements' => 'PHP 8.2+',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 2,
        ]);

        // 2. Draft Vacancy (not published)
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $draftVacancy = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'React Developer',
            'department' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Kerja React',
            'job_requirements' => 'ReactJS',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Ready to Publish',
            'quota' => 1,
        ]);

        // 3. Vacancy with 0 quota
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $noQuotaVacancy = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'DevOps Engineer',
            'department' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Kerja DevOps',
            'job_requirements' => 'AWS',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 0,
        ]);

        // 4. Vacancy with expired deadline
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $expiredVacancy = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'UI Designer',
            'department' => 'Design',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Kerja UI',
            'job_requirements' => 'Figma',
            'employment_type' => 'contract',
            'location' => 'on-site',
            'application_deadline' => Carbon::yesterday()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->assertSee($validVacancy->job_title)
            ->assertDontSee($draftVacancy->job_title)
            ->assertDontSee($noQuotaVacancy->job_title)
            ->assertDontSee($expiredVacancy->job_title);
    }

    public function test_filters_by_search_keyword()
    {
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $job1 = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'iOS Developer',
            'department' => 'Mobile',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Swift',
            'job_requirements' => 'iOS SDK',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $job2 = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Android Specialist',
            'department' => 'Mobile',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Kotlin',
            'job_requirements' => 'Android SDK',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('search', 'iOS')
            ->assertSee($job1->job_title)
            ->assertDontSee($job2->job_title);
    }

    public function test_filters_by_tipe_kerja_and_lokasi()
    {
        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $fullTimeRemote = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Backend Engineer',
            'department' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'NodeJS',
            'job_requirements' => 'Javascript',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $contractOnSite = Vacancy::create([
            'rr_id' => \App\Models\Rr::latest('id')->first()->id,
            'job_title' => 'Frontend Consultant',
            'department' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'VueJS',
            'job_requirements' => 'Vue',
            'employment_type' => 'contract',
            'location' => 'on-site',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'quota' => 1,
        ]);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('selectedTipeKerja', 'full-time')
            ->assertSee($fullTimeRemote->job_title)
            ->assertDontSee($contractOnSite->job_title);

        Livewire::test(\App\Livewire\Cw\PublicJobList::class)
            ->set('selectedLokasi', 'on-site')
            ->assertSee($contractOnSite->job_title)
            ->assertDontSee($fullTimeRemote->job_title);
    }

    public function test_logged_in_user_sees_logged_in_view()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $this->actingAs($user)
            ->get(route('candidate.jobs'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Cw\CandidateJobList::class);
    }

    public function test_logged_in_checkbox_department_filter()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $rr2 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Cloud Architect',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Cloud infra',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $itJob = Vacancy::create([
            'rr_id' => $rr2->id,
            'job_title'             => 'Cloud Architect',
            'department'          => 'IT',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Cloud infra',
            'job_requirements' => 'AWS',
            'employment_type'          => 'full-time',
            'location'              => 'remote',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'quota'               => 1,
        ]);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $rr3 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Financial Analyst',
            'department' => 'Finance',
            'status' => 'Published',
            'job_description' => 'Finance analysis',
            'employment_type' => 'full-time',
            'location' => 'on-site',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $financeJob = Vacancy::create([
            'rr_id' => $rr3->id,
            'job_title'             => 'Financial Analyst',
            'department'          => 'Finance',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Finance analysis',
            'job_requirements' => 'Excel',
            'employment_type'          => 'full-time',
            'location'              => 'on-site',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'quota'               => 1,
        ]);

        Livewire::actingAs($user)->test(\App\Livewire\Cw\CandidateJobList::class)
            ->set('selectedDepartments', ['IT'])
            ->assertSee($itJob->job_title)
            ->assertDontSee($financeJob->job_title);
    }

    public function test_logged_in_checkbox_type_filter()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $rr4 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Data Engineer',
            'department' => 'Data',
            'status' => 'Published',
            'job_description' => 'ETL pipelines',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $fullTime = Vacancy::create([
            'rr_id' => $rr4->id,
            'job_title'             => 'Data Engineer',
            'department'          => 'Data',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'ETL pipelines',
            'job_requirements' => 'Python',
            'employment_type'          => 'full-time',
            'location'              => 'remote',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'quota'               => 1,
        ]);

        $rr_temp = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Test Jabatan',
            'department' => 'IT',
            'status' => 'Published',
            'job_description' => 'Test Desc',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota' => 1,
        ]);
        $rr5 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'QA Contractor',
            'department' => 'QA',
            'status' => 'Published',
            'job_description' => 'Testing',
            'employment_type' => 'contract',
            'location' => 'on-site',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $contract = Vacancy::create([
            'rr_id' => $rr5->id,
            'job_title'             => 'QA Contractor',
            'department'          => 'QA',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'job_description' => 'Testing',
            'job_requirements' => 'Selenium',
            'employment_type'          => 'contract',
            'location'              => 'on-site',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'quota'               => 1,
        ]);

        Livewire::actingAs($user)->test(\App\Livewire\Cw\CandidateJobList::class)
            ->set('selectedTypes', ['contract'])
            ->assertSee($contract->job_title)
            ->assertDontSee($fullTime->job_title);
    }

    public function test_sorting_order()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $rr6 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Senior Researcher',
            'department' => 'RnD',
            'status' => 'Published',
            'job_description' => 'Research',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $olderData = [
            'rr_id' => $rr6->id,
            'job_title'              => 'Senior Researcher',
            'department'           => 'RnD',
            'expected_join_date'   => now()->addMonths(2)->format('Y-m-d'),
            'job_description'  => 'Research',
            'job_requirements'=> 'PhD',
            'employment_type'           => 'full-time',
            'location'               => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status'               => 'Published',
            'quota'                => 1,
        ];
        $newerData = array_merge($olderData, [
            'job_title' => 'Junior Researcher',
            'job_requirements' => 'Bachelor',
        ]);

        $older = Vacancy::create($olderData);
        $rr7 = \App\Models\Rr::create([
            'mpp_id' => $this->mpp->id,
            'job_title' => 'Junior Researcher',
            'department' => 'RnD',
            'status' => 'Published',
            'job_description' => 'Research',
            'employment_type' => 'full-time',
            'location' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'quota' => 1,
        ]);
        $newerData['rr_id'] = $rr7->id;
        $newer = Vacancy::create($newerData);

        // Force created_at via raw DB to bypass Eloquent timestamp guard
        \Illuminate\Support\Facades\DB::table('vacancies')
            ->where('id', $older->id)
            ->update(['created_at' => now()->subDays(10)]);
        \Illuminate\Support\Facades\DB::table('vacancies')
            ->where('id', $newer->id)
            ->update(['created_at' => now()]);

        // Newest first → Junior appears before Senior
        $component = Livewire::actingAs($user)->test(\App\Livewire\Cw\CandidateJobList::class)->set('sortBy', 'newest');
        $html = $component->html();
        $posNewer = strpos($html, $newer->job_title);
        $posOlder = strpos($html, $older->job_title);
        $this->assertLessThan($posOlder, $posNewer, 'Terbaru harus muncul lebih awal.');

        // Oldest first → Senior appears before Junior
        $component2 = Livewire::actingAs($user)->test(\App\Livewire\Cw\CandidateJobList::class)->set('sortBy', 'oldest');
        $html2 = $component2->html();
        $posNewer2 = strpos($html2, $newer->job_title);
        $posOlder2 = strpos($html2, $older->job_title);
        $this->assertLessThan($posNewer2, $posOlder2, 'Terlama harus muncul lebih awal.');
    }
}
