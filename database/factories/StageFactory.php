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
            'nama' => $this->faker->word() . ' Stage',
            'deskripsi' => $this->faker->sentence(),
            'butuh_scorecard' => $this->faker->boolean(),
            'butuh_jadwal' => $this->faker->boolean(),
            'urutan' => $this->faker->numberBetween(1, 10),
            'scorecard_kriteria' => null,
            'tipe_wawancara' => $this->faker->randomElement(['online', 'offline', 'hybrid']),
            'lokasi_default' => $this->faker->address(),
            'tautan_virtual_default' => 'https://meet.google.com/' . $this->faker->uuid(),
        ];
    }
}
