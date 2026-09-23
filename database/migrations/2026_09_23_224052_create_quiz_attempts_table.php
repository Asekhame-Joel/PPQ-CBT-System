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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_attempt_id')
                ->nullable()
                ->constrained('quiz_attempts')
                ->nullOnDelete();
            $table->string('attempt_type', 30)->default('new');
            $table->unsignedSmallInteger('question_count');
            $table->unsignedSmallInteger('duration_minutes');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 30)->default('in_progress');
            $table->unsignedSmallInteger('correct_count')->nullable();
            $table->unsignedSmallInteger('incorrect_count')->nullable();
            $table->unsignedSmallInteger('unanswered_count')->nullable();
            $table->decimal('score_percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
