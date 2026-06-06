<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RecruitmentRequest;
use App\Models\Mpp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecruitmentRequest>
 */
class RecruitmentRequestFactory extends Factory
{
    protected $model = RecruitmentRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mpp_id' => Mpp::factory(),
            'kuota' => $this->faker->numberBetween(1, 5),
            'jabatan' => $this->faker->jobTitle(),
            'departemen' => $this->faker->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
            'estimasi_gaji_min' => $this->faker->numberBetween(4000000, 6000000),
            'estimasi_gaji_max' => $this->faker->numberBetween(7000000, 15000000),
            'expected_join_date' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'deskripsi_pekerjaan' => $this->faker->paragraphs(3, true),
            'spesifikasi_kebutuhan' => $this->faker->paragraphs(2, true),
            'tipe_kerja' => $this->faker->randomElement(['full-time', 'contract']),
            'lokasi' => $this->faker->randomElement(['remote', 'on-site']),
            'application_deadline' => $this->faker->dateTimeBetween('+1 week', '+4 weeks')->format('Y-m-d'),
            'tampilkan_gaji' => $this->faker->boolean(),
            'status' => $this->faker->randomElement([\App\Enums\RrStatus::DRAFT, \App\Enums\RrStatus::READY_TO_PUBLISH, \App\Enums\RrStatus::PUBLISHED, \App\Enums\RrStatus::COMPLETED, \App\Enums\RrStatus::CLOSED]),
        ];
    }
}
