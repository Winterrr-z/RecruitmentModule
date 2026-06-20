<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Stage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Stage',
            'description' => fake()->sentence(),
            'needs_scorecard' => fake()->boolean(),
            'needs_schedule' => fake()->boolean(),
            'sequence' => fake()->numberBetween(1, 10),
            'scorecard_criteria' => null,
            'interview_type' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'default_location' => fake()->address(),
            'default_virtual_link' => 'https://meet.google.com/' . fake()->uuid(),
        ];
    }
}
