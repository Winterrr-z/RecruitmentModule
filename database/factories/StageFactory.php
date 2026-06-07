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
            'name' => $this->faker->word() . ' Stage',
            'description' => $this->faker->sentence(),
            'needs_scorecard' => $this->faker->boolean(),
            'needs_schedule' => $this->faker->boolean(),
            'sequence' => $this->faker->numberBetween(1, 10),
            'scorecard_criteria' => null,
            'interview_type' => $this->faker->randomElement(['online', 'offline', 'hybrid']),
            'default_location' => $this->faker->address(),
            'default_virtual_link' => 'https://meet.google.com/' . $this->faker->uuid(),
        ];
    }
}
