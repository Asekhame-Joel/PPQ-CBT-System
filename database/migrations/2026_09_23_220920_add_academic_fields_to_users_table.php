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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable();
            $table->string('role', 20)->default('student');
            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('level_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->boolean('is_active')->default(true);

            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'is_active']);
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('level_id');
            $table->dropColumn(['phone', 'role', 'is_active']);
        });
    }
};
