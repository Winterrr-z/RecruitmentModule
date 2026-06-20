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
            'plan_name' => 'MPP ' . fake()->jobTitle(),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'job_title' => fake()->jobTitle(),
            'quota' => fake()->numberBetween(1, 10),
            'estimated_salary_min' => fake()->numberBetween(4000000, 6000000),
            'estimated_salary_max' => fake()->numberBetween(7000000, 15000000),
            'sla_days' => fake()->numberBetween(14, 45),
            'absolute_target_date' => fake()->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'status' => fake()->randomElement([\App\Enums\MppStatus::DRAFT, \App\Enums\MppStatus::APPROVED, \App\Enums\MppStatus::COMPLETED, \App\Enums\MppStatus::CLOSED]),
            'note' => fake()->sentence(),
            'last_activity_at' => now(),
        ];
    }
}
