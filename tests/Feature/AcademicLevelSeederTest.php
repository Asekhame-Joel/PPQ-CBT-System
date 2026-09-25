<?php

namespace Tests\Feature;

use Database\Seeders\AcademicLevelSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AcademicLevelSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_standard_academic_levels_are_seeded_idempotently(): void
    {
        $this->seed(AcademicLevelSeeder::class);
        $this->seed(AcademicLevelSeeder::class);

        $this->assertDatabaseCount('levels', 6);
        $this->assertDatabaseHas('levels', [
            'name' => '100 Level',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('levels', [
            'name' => '600 Level',
            'sort_order' => 6,
            'is_active' => true,
        ]);
    }
}
