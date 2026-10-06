<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_level', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();

            $table->primary(['course_id', 'level_id']);
        });

        DB::table('courses')
            ->orderBy('id')
            ->each(function (object $course): void {
                DB::table('course_level')->insertOrIgnore([
                    'course_id' => $course->id,
                    'level_id' => $course->level_id,
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_level');
    }
};
