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
            'job_title' => $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'estimated_salary_min' => $this->faker->numberBetween(4000000, 6000000),
            'estimated_salary_max' => $this->faker->numberBetween(7000000, 15000000),
            'job_description' => $this->faker->paragraphs(3, true),
            'job_requirements' => $this->faker->paragraphs(2, true),
            'employment_type' => $this->faker->randomElement(['full-time', 'contract']),
            'location' => $this->faker->randomElement(['remote', 'on-site']),
            'application_deadline' => $this->faker->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'show_salary' => $this->faker->boolean(),
            'status' => \App\Enums\VacancyStatus::PUBLISHED,
            'quota' => $this->faker->numberBetween(1, 5),
        ];
    }
}
