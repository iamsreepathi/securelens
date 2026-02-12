<?php

namespace Database\Seeders;

use App\Models\DeadLetterJob;
use Illuminate\Database\Seeder;

class DeadLetterJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DeadLetterJob::factory()->count(2)->create();
    }
}
