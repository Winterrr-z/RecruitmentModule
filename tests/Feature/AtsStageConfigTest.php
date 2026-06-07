<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AtsStageConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_stages_page_requires_auth()
    {
        $this->get(route('ats.stages'))
            ->assertRedirect(route('login'));
    }

    public function test_authorized_user_can_access_stages_page()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $this->actingAs($user)
            ->get(route('ats.stages'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Ats\AtsStageConfig::class);
    }

    public function test_can_create_stage()
    {
        $user = User::factory()->create(['role' => 'hr']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->set('name', 'Interview HR')
            ->set('description', 'Interview dengan tim HR')
            ->set('needs_scorecard', true)
            ->set('scorecardKriteria', [['criteria' => 'Keahlian Teknis', 'weight' => 100]])
            ->set('needs_schedule', true)
            ->set('interview_type', 'online')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('stages', [
            'name' => 'Interview HR',
            'description' => 'Interview dengan tim HR',
            'needs_scorecard' => true,
            'needs_schedule' => true,
            'sequence' => 2,
        ]);
    }

    public function test_can_edit_stage()
    {
        $user = User::factory()->create(['role' => 'hr']);
        
        $stage = Stage::create([
            'name' => 'Test Stage',
            'description' => 'A test stage',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('editStage', $stage->id)
            ->assertSet('name', 'Test Stage')
            ->set('name', 'Test Stage Edited')
            ->set('needs_scorecard', true)
            ->set('scorecardKriteria', [['criteria' => 'Keahlian Teknis', 'weight' => 100]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('stages', [
            'id' => $stage->id,
            'name' => 'Test Stage Edited',
            'needs_scorecard' => true,
        ]);
    }

    public function test_cannot_delete_default_stages()
    {
        $user = User::factory()->create(['role' => 'hr']);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('deleteStage', 1) // Applied
            ->assertSee('Stage Applied dan Final tidak dapat dihapus.');

        $this->assertDatabaseHas('stages', ['id' => 1]);
    }

    public function test_can_delete_custom_stage()
    {
        $user = User::factory()->create(['role' => 'hr']);
        
        $stage = Stage::create([
            'name' => 'Custom Stage',
            'description' => 'Will be deleted',
            'needs_scorecard' => false,
            'needs_schedule' => false,
            'sequence' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('deleteStage', $stage->id)
            ->assertSee('Stage berhasil dihapus.');

        $this->assertDatabaseMissing('stages', ['id' => $stage->id]);
    }

    public function test_can_reorder_stages()
    {
        $user = User::factory()->create(['role' => 'hr']);
        
        $stage3 = Stage::create([
            'name' => 'Stage 3',
            'sequence' => 3,
        ]);

        $stage4 = Stage::create([
            'name' => 'Stage 4',
            'sequence' => 4,
        ]);

        // Move Stage 4 up (swapping with Stage 3)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveUp', $stage4->id);

        $this->assertEquals(2, $stage4->fresh()->sequence);
        $this->assertEquals(3, $stage3->fresh()->sequence);
    }

    public function test_cannot_reorder_system_stages()
    {
        $user = User::factory()->create(['role' => 'hr']);

        // Try moving Applied (ID 1)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveUp', 1)
            ->assertSee('Urutan stage Applied dan Final tidak dapat diubah.');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveDown', 1)
            ->assertSee('Urutan stage Applied dan Final tidak dapat diubah.');

        // Try moving Final (ID 2)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveUp', 2)
            ->assertSee('Urutan stage Applied dan Final tidak dapat diubah.');

        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveDown', 2)
            ->assertSee('Urutan stage Applied dan Final tidak dapat diubah.');
    }

    public function test_cannot_move_custom_stage_before_applied()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $customStage = Stage::create([
            'name' => 'Custom 1',
            'sequence' => 2,
        ]);

        // Try moving Custom 1 up (which would swap with Applied at urutan 1)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveUp', $customStage->id)
            ->assertSee('Tidak dapat memindahkan stage sebelum Applied.');

        $this->assertEquals(2, $customStage->fresh()->sequence);
    }

    public function test_cannot_move_custom_stage_after_final()
    {
        $user = User::factory()->create(['role' => 'hr']);

        $customStage = Stage::create([
            'name' => 'Custom 1',
            'sequence' => 2,
        ]);

        // Try moving Custom 1 down (which would swap with Final at urutan 3)
        Livewire::actingAs($user)
            ->test(\App\Livewire\Ats\AtsStageConfig::class)
            ->call('moveDown', $customStage->id)
            ->assertSee('Tidak dapat memindahkan stage setelah Final.');

        $this->assertEquals(2, $customStage->fresh()->sequence);
    }
}
