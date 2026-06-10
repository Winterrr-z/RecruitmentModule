<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Vacancy;
use App\Models\Candidate;
use App\Models\Scorecard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsScorecardFormTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Stage $stage;
    private Candidate $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        // Stage dengan scorecard_criteria template — gunakan create() langsung agar
        // scorecard_criteria pasti tersimpan tanpa bergantung pada firstOrCreate
        // yang bisa mengembalikan record id=1 dari test class lain.
        $this->stage = Stage::create([
            'name'               => 'Psikotest Lanjut',
            'description'        => 'Tahap tes psikologi lanjut',
            'sequence'           => 98,
            'needs_scorecard'    => true,
            'scorecard_criteria' => [
                ['criteria' => 'Kemampuan Verbal',  'weight' => 40],
                ['criteria' => 'Kemampuan Numerik', 'weight' => 60],
            ],
        ]);

        $mpp = \App\Models\Mpp::create([
            'plan_name'            => 'MPP Scorecard Test',
            'department'           => 'Finance',
            'job_title'            => 'Analyst',
            'quota'                => 1,
            'sla_days'             => 30,
            'absolute_target_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $rr = \App\Models\Rr::create([
            'mpp_id'               => $mpp->id,
            'job_title'            => 'Analyst',
            'department'           => 'Finance',
            'status'               => 'Published',
            'job_description'      => 'Desc',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        $vacancy = Vacancy::create([
            'rr_id'                => $rr->id,
            'job_title'            => 'Analyst',
            'department'           => 'Finance',
            'status'               => 'Published',
            'expected_join_date'   => now()->addDays(60)->format('Y-m-d'),
            'job_description'      => 'Job description',
            'job_requirements'     => 'Job requirements',
            'employment_type'      => 'full-time',
            'location'             => 'on-site',
            'application_deadline' => now()->addDays(15)->format('Y-m-d'),
            'quota'                => 1,
        ]);

        $this->candidate = Candidate::create([
            'vacancy_id'       => $vacancy->id,
            'name'             => 'Scoreable Candidate',
            'email'            => 'score@example.com',
            'phone'            => '0822222222',
            'current_stage_id' => $this->stage->id,
            'status'           => 'Applied',
        ]);
    }

    /** mount() memuat template dari Stage jika belum ada scorecard */
    public function test_mount_loads_stage_template_when_no_existing_scorecard(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ]);

        $kriteriaList = $component->get('kriteriaList');
        $this->assertCount(2, $kriteriaList);
        $this->assertEquals('Kemampuan Verbal', $kriteriaList[0]['criteria']);
        $this->assertEquals(40, $kriteriaList[0]['weight']);
        $this->assertEquals(0, $kriteriaList[0]['score']); // default
    }

    /** mount() memuat scorecard yang sudah ada dari DB */
    public function test_mount_loads_existing_scorecards_from_db(): void
    {
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Verbal',
            'weight'       => 40,
            'score'        => 75,
        ]);
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Numerik',
            'weight'       => 60,
            'score'        => 80,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ]);

        $kriteriaList = $component->get('kriteriaList');
        $this->assertCount(2, $kriteriaList);
        $this->assertEquals(75, $kriteriaList[0]['score']);
        $this->assertEquals(80, $kriteriaList[1]['score']);
    }

    /** calculateTotals() menghitung weighted score dengan benar */
    public function test_calculate_totals_computes_correctly(): void
    {
        // Bobot: 40+60=100, (75*40 + 80*60)/100 = (3000+4800)/100 = 78
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Verbal',
            'weight'       => 40,
            'score'        => 75,
        ]);
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Numerik',
            'weight'       => 60,
            'score'        => 80,
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ])
            ->assertSet('totalBobot', 100)
            ->assertSet('totalWeightedScore', 78.0);
    }

    /** save() gagal jika kriteriaList kosong */
    public function test_save_fails_when_kriteria_list_empty(): void
    {
        // Stage tanpa template
        $emptyStage = Stage::create([
            'name'               => 'Empty Stage',
            'description'        => 'No criteria',
            'sequence'           => 99,
            'needs_scorecard'    => true,
            'scorecard_criteria' => [],
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $emptyStage->id,
            ])
            ->call('save')
            ->assertHasErrors(['kriteriaList']);
    }

    /** save() gagal jika ada score yang di luar rentang 1-100 */
    public function test_save_fails_when_score_is_out_of_range(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ]);

        $kriteriaList = $component->get('kriteriaList');
        $kriteriaList[0]['score'] = 0; // nilai 0 = di luar 1-100
        $kriteriaList[1]['score'] = 80;

        $component->set('kriteriaList', $kriteriaList)
            ->call('save')
            ->assertHasErrors(['kriteriaList.0.score']);
    }

    /**
     * save() berhasil: record lama dihapus dan record baru disimpan dari template.
     * Kita buat 1 scorecard lama secara manual dengan criteria yang SAMA dengan template
     * agar setelah save() jumlah tetap 2 (template = 2 kriteria).
     */
    public function test_save_deletes_old_and_inserts_new_scorecards(): void
    {
        // Buat 1 scorecard lama dengan kriteria berbeda (simulasi data basi)
        Scorecard::create([
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Verbal',
            'weight'       => 40,
            'score'        => 50, // nilai lama
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsScorecardForm::class, [
                'candidateId' => $this->candidate->id,
                'stageId'     => $this->stage->id,
            ]);

        // mount() menemukan 1 scorecard lama → load dari DB (bukan template)
        // Update semua score ke nilai valid
        $kriteriaList = $component->get('kriteriaList');
        foreach ($kriteriaList as $i => $item) {
            $kriteriaList[$i]['score'] = 85;
        }

        $component->set('kriteriaList', $kriteriaList)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('ats.candidate.detail', ['candidateId' => $this->candidate->id]));

        // Harus hanya tersisa 1 record (yang lama = 1, dihapus, lalu insert kembali 1)
        $this->assertEquals(1, Scorecard::where('candidate_id', $this->candidate->id)
            ->where('stage_id', $this->stage->id)
            ->count());

        // Score sekarang 85
        $this->assertDatabaseHas('scorecards', [
            'candidate_id' => $this->candidate->id,
            'stage_id'     => $this->stage->id,
            'criteria'     => 'Kemampuan Verbal',
            'score'        => 85,
        ]);
    }

    /** Halaman form scorecard memerlukan autentikasi */
    public function test_scorecard_form_page_requires_auth(): void
    {
        $this->get(route('ats.candidate.scorecard', [
            'candidateId' => $this->candidate->id,
            'stageId'     => $this->stage->id,
        ]))->assertRedirect(route('login'));
    }
}
