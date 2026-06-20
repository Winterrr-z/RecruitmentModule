<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rr;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rr>
 */
class RrFactory extends Factory
{
    protected $model = Rr::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mpp_id' => Mpp::factory(),
            'title' => fn (array $attributes) => 'Rekrutmen ' . $attributes['job_title'],
            'quota' => fake()->numberBetween(1, 5),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'estimated_salary_min' => fake()->numberBetween(4000000, 6000000),
            'estimated_salary_max' => fake()->numberBetween(7000000, 15000000),
            'expected_join_date' => fake()->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'job_description' => fake()->paragraphs(3, true),
            'job_requirements' => fake()->paragraphs(2, true),
            'employment_type' => fake()->randomElement(['full-time', 'contract']),
            'location' => fake()->randomElement(['remote', 'on-site']),
            'application_deadline' => fake()->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'show_salary' => fake()->boolean(),
            'status' => fake()->randomElement([\App\Enums\RrStatus::READY_TO_PUBLISH, \App\Enums\RrStatus::PUBLISHED, \App\Enums\RrStatus::COMPLETED, \App\Enums\RrStatus::CLOSED]),
        ];
    }
}
