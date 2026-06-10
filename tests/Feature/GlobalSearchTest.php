<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'hr']);
    }

    /** Query kosong mengembalikan array kosong */
    public function test_empty_query_returns_no_results(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', '')
            ->assertSet('searchResults', []);
    }

    /** Query spasi saja mengembalikan array kosong */
    public function test_whitespace_only_query_returns_no_results(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', '   ')
            ->assertSet('searchResults', []);
    }

    /** Search menemukan fitur statis (navigasi) */
    public function test_search_finds_static_feature_by_keyword(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'dashboard');

        $results = $component->get('searchResults');
        $this->assertNotEmpty($results);
        $titles = array_column($results, 'title');
        $this->assertContains('Dashboard Utama', $titles);
    }

    /** Search menemukan kandidat berdasarkan nama */
    public function test_search_finds_candidate_by_name(): void
    {
        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP Global Test',
            'department'           => 'IT',
            'job_title'            => 'Dev',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Dev',
            'department'           => 'IT',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);
        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Dev',
            'department'           => 'IT',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Budi Santoso',
            'email'            => 'budi@example.com',
            'phone'            => '0811111111',
            'current_stage_id' => 1,
            'status'           => 'Applied',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Budi');

        $results = $component->get('searchResults');
        $titles  = array_column($results, 'title');
        $this->assertTrue(
            collect($titles)->contains(fn ($t) => str_contains($t, 'Budi Santoso')),
            'Kandidat Budi Santoso tidak ditemukan di hasil search'
        );
    }

    /** Search menemukan kandidat berdasarkan email */
    public function test_search_finds_candidate_by_email(): void
    {
        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP Email Test',
            'department'           => 'HR',
            'job_title'            => 'Staff',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Staff',
            'department'           => 'HR',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);
        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Staff',
            'department'           => 'HR',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Email Tester',
            'email'            => 'unik.email@perusahaan.co.id',
            'phone'            => '0833333333',
            'current_stage_id' => 1,
            'status'           => 'Applied',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'unik.email');

        $results = $component->get('searchResults');
        $titles  = array_column($results, 'title');
        $this->assertTrue(
            collect($titles)->contains(fn ($t) => str_contains($t, 'Email Tester')),
            'Kandidat dengan email unik tidak ditemukan'
        );
    }

    /** Search menemukan blacklist berdasarkan nama */
    public function test_search_finds_blacklist_entry(): void
    {
        Blacklist::create([
            'name'   => 'Penipu Ulung',
            'email'  => 'penipu@contoh.com',
            'phone'  => '0844444444',
            'reason' => 'Data palsu',
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Penipu');

        $results = $component->get('searchResults');
        $titles  = array_column($results, 'title');
        $this->assertTrue(
            collect($titles)->contains(fn ($t) => str_contains($t, 'Penipu Ulung')),
            'Entry blacklist tidak ditemukan di hasil search'
        );
    }

    /** Search menemukan MPP berdasarkan plan_name */
    public function test_search_finds_mpp_by_plan_name(): void
    {
        \App\Models\Mpp::create([
            'plan_name'            => 'Plan Rekrut Khusus 2099',
            'department'           => 'Marketing',
            'job_title'            => 'Marketing Manager',
            'quota'                => 3,
            'sla_days'             => 60,
            'absolute_target_date' => now()->addDays(60)->format('Y-m-d'),
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Rekrut Khusus');

        $results = $component->get('searchResults');
        $titles  = array_column($results, 'title');
        $this->assertTrue(
            collect($titles)->contains(fn ($t) => str_contains($t, 'Plan Rekrut Khusus 2099')),
            'MPP tidak ditemukan di hasil search'
        );
    }

    /** Search menemukan RR berdasarkan job_title */
    public function test_search_finds_rr_by_job_title(): void
    {
        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP RR Search',
            'department'           => 'Legal',
            'job_title'            => 'Legal Advisor',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Legal Advisor Unik',
            'department'           => 'Legal',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'Legal Advisor Unik');

        $results = $component->get('searchResults');
        $titles  = array_column($results, 'title');
        $this->assertTrue(
            collect($titles)->contains(fn ($t) => str_contains($t, 'Legal Advisor Unik')),
            'RR tidak ditemukan di hasil search'
        );
    }

    /**
     * Hasil dibatasi maksimal 8 item.
     * Buat 10 kandidat dengan nama unik agar ada >8 kandidat yang cocok,
     * sehingga cap benar-benar diuji (bukan hanya kebetulan hasilnya < 8).
     */
    public function test_search_results_are_capped_at_8(): void
    {
        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP Cap Test',
            'department'           => 'IT',
            'job_title'            => 'Dev',
            'quota'                => 10,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Dev',
            'department'           => 'IT',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 10,
        ]);
        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Dev',
            'department'           => 'IT',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Desc',
            'job_requirements'     => 'Req',
            'employment_type'      => 'full-time',
            'location'             => 'remote',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 10,
        ]);

        // Buat 10 kandidat yang SEMUA cocok dengan query 'UniqueCapTest'
        for ($i = 1; $i <= 10; $i++) {
            Candidate::create([
                'vacancy_id'       => $vacancy->id,
                'name'             => "UniqueCapTest Kandidat $i",
                'email'            => "cap{$i}@example.com",
                'phone'            => "080000000{$i}",
                'current_stage_id' => 1,
                'status'           => 'Applied',
            ]);
        }

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\GlobalSearch::class)
            ->set('query', 'UniqueCapTest');

        $results = $component->get('searchResults');

        // Pastikan ada >8 kandidat di DB yang cocok (sehingga cap memang diuji)
        $this->assertGreaterThan(8, Candidate::where('name', 'like', '%UniqueCapTest%')->count(),
            'Perlu >8 kandidat cocok agar cap benar-benar diuji');

        // Hasil yang dikembalikan komponen harus dibatasi <= 8
        $this->assertLessThanOrEqual(8, count($results),
            'GlobalSearch tidak membatasi hasil ke 8 item');
    }
}
