<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vacancy;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rr_id' => \App\Models\Rr::factory(),
            'title' => fn (array $attributes) => 'Lowongan ' . $attributes['job_title'],
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'estimated_salary_min' => fake()->numberBetween(4000000, 6000000),
            'estimated_salary_max' => fake()->numberBetween(7000000, 15000000),
            'job_description' => fake()->paragraphs(3, true),
            'job_requirements' => fake()->paragraphs(2, true),
            'employment_type' => fake()->randomElement(['full-time', 'contract']),
            'location' => fake()->randomElement(['remote', 'on-site']),
            'application_deadline' => fake()->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'show_salary' => fake()->boolean(),
            'status' => \App\Enums\VacancyStatus::PUBLISHED,
            'quota' => fake()->numberBetween(1, 5),
        ];
    }
}
