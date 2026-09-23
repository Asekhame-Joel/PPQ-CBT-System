<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedSmallInteger('default_question_count');
            $table->unsignedSmallInteger('default_duration');
            $table->unsignedSmallInteger('min_question_count');
            $table->unsignedSmallInteger('max_question_count');
            $table->unsignedSmallInteger('min_duration');
            $table->unsignedSmallInteger('max_duration');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
