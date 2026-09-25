<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class AcademicLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([100, 200, 300, 400, 500, 600] as $sortOrder => $level) {
            Level::query()->updateOrCreate(
                ['name' => "{$level} Level"],
                ['sort_order' => $sortOrder + 1, 'is_active' => true],
            );
        }
    }
}
