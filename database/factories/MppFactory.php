<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mpp>
 */
class MppFactory extends Factory
{
    protected $model = Mpp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_name' => 'MPP ' . $this->faker->jobTitle(),
            'department' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'job_title' => $this->faker->jobTitle(),
            'quota' => $this->faker->numberBetween(1, 10),
            'estimated_salary_min' => $this->faker->numberBetween(4000000, 6000000),
            'estimated_salary_max' => $this->faker->numberBetween(7000000, 15000000),
            'sla_days' => $this->faker->numberBetween(14, 45),
            'absolute_target_date' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement([\App\Enums\MppStatus::DRAFT, \App\Enums\MppStatus::APPROVED, \App\Enums\MppStatus::COMPLETED, \App\Enums\MppStatus::CLOSED]),
            'note' => $this->faker->sentence(),
            'last_activity_at' => now(),
        ];
    }
}
