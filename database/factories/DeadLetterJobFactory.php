<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeadLetterJob>
 */
class DeadLetterJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'connection' => fake()->randomElement(['redis', 'database']),
            'queue' => fake()->randomElement(['default', 'ingestion', 'notifications']),
            'job_uuid' => (string) fake()->uuid(),
            'job_name' => 'App\\Jobs\\ProcessQueuedTask',
            'payload' => [
                'uuid' => (string) fake()->uuid(),
                'displayName' => 'App\\Jobs\\ProcessQueuedTask',
                'data' => ['attempt' => fake()->numberBetween(1, 5)],
            ],
            'exception' => 'RuntimeException: Simulated queue failure',
            'failed_at' => now(),
        ];
    }
}
