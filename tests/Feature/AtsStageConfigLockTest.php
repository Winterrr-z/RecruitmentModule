<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use App\Models\Candidate;
use App\Models\Scorecard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsStageConfigLockTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'hr']);

        // Let's retrieve a stage (stage ID 1 is Applied, stage ID 2 is Final).
        // Let's create a custom stage for testing scorecard config.
        $this->stage = Stage::create([
            'name' => 'Technical Test',
            'description' => 'Coding test',
            'needs_scorecard' => true,
            'needs_schedule' => false,
            'sequence' => 2,
            'scorecard_criteria' => [
                ['criteria' => 'Logic', 'weight' => 50],
                ['criteria' => 'Syntax', 'weight' => 50],
            ],
        ]);
    }

    public function test_can_edit_scorecard_when_no_evaluations_exist()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('editStage', $this->stage->id)
            ->assertSet('isScorecardLocked', false)
            ->set('form.scorecardKriteria', [
                ['criteria' => 'New Criteria', 'weight' => 100],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals([
            ['criteria' => 'New Criteria', 'weight' => 100],
        ], $this->stage->fresh()->scorecard_criteria);
    }

    public function test_cannot_edit_scorecard_when_evaluations_exist()
    {
        // Create a candidate and a scorecard evaluation for this stage
        $candidate = Candidate::create([
            'vacancy_id' => null,
            'name' => 'John Applicant',
            'email' => 'john.app@example.com',
            'phone' => '0811111111',
            'current_stage_id' => $this->stage->id,
            'status' => 'Applied',
        ]);

        Scorecard::create([
            'candidate_id' => $candidate->id,
            'stage_id' => $this->stage->id,
            'criteria' => 'Logic',
            'weight' => 50,
            'score' => 8,
        ]);

        // Attempting to modify the scorecard criteria should fail validation
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('editStage', $this->stage->id)
            ->assertSet('isScorecardLocked', true)
            ->set('form.scorecardKriteria', [
                ['criteria' => 'Modified Logic', 'weight' => 50],
                ['criteria' => 'Syntax', 'weight' => 50],
            ])
            ->call('save')
            ->assertHasErrors(['form.scorecardKriteria' => 'Tidak bisa merubah scorecard karena sudah ada kandidat yang dinilai.']);

        // Verify criteria remained unchanged in the database
        $this->assertEquals([
            ['criteria' => 'Logic', 'weight' => 50],
            ['criteria' => 'Syntax', 'weight' => 50],
        ], $this->stage->fresh()->scorecard_criteria);
    }
}
