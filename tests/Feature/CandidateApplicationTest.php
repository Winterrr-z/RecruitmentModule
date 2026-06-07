<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lowongan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Cw\CandidateJobDetail;

class CandidateApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_cannot_upload_invalid_file_type_for_cv()
    {
        // Mock a user
        $user = User::factory()->create(['role' => 'applicant']);
        
        // Mock a published job
        $lowongan = Lowongan::factory()->create(['status' => 'Published']);

        // Create a fake invalid file pretending to be PDF
        $invalidFile = UploadedFile::fake()->create('malicious.pdf', 100, 'text/plain');

        Livewire::actingAs($user)
            ->test(CandidateJobDetail::class, ['id' => $lowongan->id])
            ->set('phone', '08123456789')
            ->set('cv', $invalidFile)
            ->call('apply')
            ->assertHasErrors(['cv' => 'mimetypes']); // Should fail mime type validation
    }

    public function test_candidate_can_upload_valid_pdf_for_cv()
    {
        $user = User::factory()->create(['role' => 'applicant']);
        $lowongan = Lowongan::factory()->create(['status' => 'Published']);

        // Create a valid fake PDF
        $validPdf = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        Livewire::actingAs($user)
            ->test(CandidateJobDetail::class, ['id' => $lowongan->id])
            ->set('phone', '08123456789')
            ->set('cv', $validPdf)
            ->call('apply')
            ->assertHasNoErrors(['cv']);
    }
}
