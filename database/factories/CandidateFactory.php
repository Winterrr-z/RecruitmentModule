<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Candidate;
use App\Models\Lowongan;
use App\Models\Stage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lowongan_id' => Lowongan::factory(),
            'user_id' => null,
            'nama' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'telepon' => $this->faker->phoneNumber(),
            'cv_path' => 'cv/sample.pdf',
            'portofolio_path' => null,
            'current_stage_id' => 1, // Default ke stage Applied yang di-seed
            'status' => $this->faker->randomElement([\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED]),
            'source' => $this->faker->randomElement(['public', 'manual']),
        ];
    }
}
