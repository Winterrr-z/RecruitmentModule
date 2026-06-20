<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Candidate;
use App\Models\Vacancy;
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
            'vacancy_id' => Vacancy::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'cv_path' => 'cv/sample.pdf',
            'portofolio_path' => null,
            'current_stage_id' => 1, // Default ke stage Applied yang di-seed
            'status' => fake()->randomElement([\App\Enums\CandidateStatus::APPLIED, \App\Enums\CandidateStatus::IN_PROGRESS, \App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::HIRED]),
            'source' => fake()->randomElement(['public', 'manual']),
        ];
    }
}
