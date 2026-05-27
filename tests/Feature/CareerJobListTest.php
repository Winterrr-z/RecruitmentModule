<?php

namespace Tests\Feature;

use App\Models\Mpp;
use App\Models\Lowongan;
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

        // Buat dummy MPP untuk relasi Lowongan
        $this->mpp = Mpp::create([
            'nama_plan' => 'Plan Karir',
            'departemen' => 'Teknologi',
            'jabatan' => 'Developer',
            'jumlah_kebutuhan' => 5,
            'sla_bulan' => 3,
            'target_waktu_absolut' => now()->addMonths(3)->format('Y-m-d'),
        ]);
    }

    public function test_careers_page_contains_livewire_component()
    {
        $this->get(route('careers'))
            ->assertSuccessful()
            ->assertSeeLivewire('public-job-list');
    }

    public function test_displays_only_published_active_jobs_with_quota_and_valid_deadline()
    {
        // 1. Valid Lowongan (Published, quota > 0, deadline in future)
        $validLowongan = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'Laravel Developer',
            'departemen' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja Laravel 12',
            'spesifikasi_kebutuhan' => 'PHP 8.2+',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 2,
        ]);

        // 2. Draft Lowongan (not published)
        $draftLowongan = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'React Developer',
            'departemen' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja React',
            'spesifikasi_kebutuhan' => 'ReactJS',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Ready to Publish',
            'kuota' => 1,
        ]);

        // 3. Lowongan with 0 quota
        $noQuotaLowongan = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'DevOps Engineer',
            'departemen' => 'IT',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja DevOps',
            'spesifikasi_kebutuhan' => 'AWS',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 0,
        ]);

        // 4. Lowongan with expired deadline
        $expiredLowongan = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'UI Designer',
            'departemen' => 'Design',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kerja UI',
            'spesifikasi_kebutuhan' => 'Figma',
            'tipe_kerja' => 'contract',
            'lokasi' => 'on-site',
            'application_deadline' => Carbon::yesterday()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        Livewire::test('public-job-list')
            ->assertSee($validLowongan->jabatan)
            ->assertDontSee($draftLowongan->jabatan)
            ->assertDontSee($noQuotaLowongan->jabatan)
            ->assertDontSee($expiredLowongan->jabatan);
    }

    public function test_filters_by_search_keyword()
    {
        $job1 = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'iOS Developer',
            'departemen' => 'Mobile',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Swift',
            'spesifikasi_kebutuhan' => 'iOS SDK',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        $job2 = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'Android Specialist',
            'departemen' => 'Mobile',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Kotlin',
            'spesifikasi_kebutuhan' => 'Android SDK',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        Livewire::test('public-job-list')
            ->set('search', 'iOS')
            ->assertSee($job1->jabatan)
            ->assertDontSee($job2->jabatan);
    }

    public function test_filters_by_tipe_kerja_and_lokasi()
    {
        $fullTimeRemote = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'Backend Engineer',
            'departemen' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'NodeJS',
            'spesifikasi_kebutuhan' => 'Javascript',
            'tipe_kerja' => 'full-time',
            'lokasi' => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        $contractOnSite = Lowongan::create([
            'mpp_id' => $this->mpp->id,
            'jabatan' => 'Frontend Consultant',
            'departemen' => 'Engineering',
            'expected_join_date' => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'VueJS',
            'spesifikasi_kebutuhan' => 'Vue',
            'tipe_kerja' => 'contract',
            'lokasi' => 'on-site',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status' => 'Published',
            'kuota' => 1,
        ]);

        Livewire::test('public-job-list')
            ->set('selectedTipeKerja', 'full-time')
            ->assertSee($fullTimeRemote->jabatan)
            ->assertDontSee($contractOnSite->jabatan);

        Livewire::test('public-job-list')
            ->set('selectedLokasi', 'on-site')
            ->assertSee($contractOnSite->jabatan)
            ->assertDontSee($fullTimeRemote->jabatan);
    }

    public function test_logged_in_user_sees_logged_in_view()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $this->actingAs($user)
            ->get(route('candidate.jobs'))
            ->assertSuccessful()
            ->assertSeeLivewire('candidate-job-list');
    }

    public function test_logged_in_checkbox_department_filter()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $itJob = Lowongan::create([
            'mpp_id'              => $this->mpp->id,
            'jabatan'             => 'Cloud Architect',
            'departemen'          => 'IT',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Cloud infra',
            'spesifikasi_kebutuhan' => 'AWS',
            'tipe_kerja'          => 'full-time',
            'lokasi'              => 'remote',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'kuota'               => 1,
        ]);

        $financeJob = Lowongan::create([
            'mpp_id'              => $this->mpp->id,
            'jabatan'             => 'Financial Analyst',
            'departemen'          => 'Finance',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Finance analysis',
            'spesifikasi_kebutuhan' => 'Excel',
            'tipe_kerja'          => 'full-time',
            'lokasi'              => 'on-site',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'kuota'               => 1,
        ]);

        Livewire::actingAs($user)->test('candidate-job-list')
            ->set('selectedDepartments', ['IT'])
            ->assertSee($itJob->jabatan)
            ->assertDontSee($financeJob->jabatan);
    }

    public function test_logged_in_checkbox_type_filter()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $fullTime = Lowongan::create([
            'mpp_id'              => $this->mpp->id,
            'jabatan'             => 'Data Engineer',
            'departemen'          => 'Data',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'ETL pipelines',
            'spesifikasi_kebutuhan' => 'Python',
            'tipe_kerja'          => 'full-time',
            'lokasi'              => 'remote',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'kuota'               => 1,
        ]);

        $contract = Lowongan::create([
            'mpp_id'              => $this->mpp->id,
            'jabatan'             => 'QA Contractor',
            'departemen'          => 'QA',
            'expected_join_date'  => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan' => 'Testing',
            'spesifikasi_kebutuhan' => 'Selenium',
            'tipe_kerja'          => 'contract',
            'lokasi'              => 'on-site',
            'application_deadline'=> Carbon::tomorrow()->format('Y-m-d'),
            'status'              => 'Published',
            'kuota'               => 1,
        ]);

        Livewire::actingAs($user)->test('candidate-job-list')
            ->set('selectedTypes', ['contract'])
            ->assertSee($contract->jabatan)
            ->assertDontSee($fullTime->jabatan);
    }

    public function test_sorting_order()
    {
        $user = \App\Models\User::factory()->create(['role' => 'applicant']);

        $olderData = [
            'mpp_id'               => $this->mpp->id,
            'jabatan'              => 'Senior Researcher',
            'departemen'           => 'RnD',
            'expected_join_date'   => now()->addMonths(2)->format('Y-m-d'),
            'deskripsi_pekerjaan'  => 'Research',
            'spesifikasi_kebutuhan'=> 'PhD',
            'tipe_kerja'           => 'full-time',
            'lokasi'               => 'remote',
            'application_deadline' => Carbon::tomorrow()->format('Y-m-d'),
            'status'               => 'Published',
            'kuota'                => 1,
        ];
        $newerData = array_merge($olderData, [
            'jabatan' => 'Junior Researcher',
            'spesifikasi_kebutuhan' => 'Bachelor',
        ]);

        $older = Lowongan::create($olderData);
        $newer = Lowongan::create($newerData);

        // Force created_at via raw DB to bypass Eloquent timestamp guard
        \Illuminate\Support\Facades\DB::table('lowongans')
            ->where('id', $older->id)
            ->update(['created_at' => now()->subDays(10)]);
        \Illuminate\Support\Facades\DB::table('lowongans')
            ->where('id', $newer->id)
            ->update(['created_at' => now()]);

        // Newest first → Junior appears before Senior
        $component = Livewire::actingAs($user)->test('candidate-job-list')->set('sortBy', 'newest');
        $html = $component->html();
        $posNewer = strpos($html, $newer->jabatan);
        $posOlder = strpos($html, $older->jabatan);
        $this->assertLessThan($posOlder, $posNewer, 'Terbaru harus muncul lebih awal.');

        // Oldest first → Senior appears before Junior
        $component2 = Livewire::actingAs($user)->test('candidate-job-list')->set('sortBy', 'oldest');
        $html2 = $component2->html();
        $posNewer2 = strpos($html2, $newer->jabatan);
        $posOlder2 = strpos($html2, $older->jabatan);
        $this->assertLessThan($posNewer2, $posOlder2, 'Terlama harus muncul lebih awal.');
    }
}
