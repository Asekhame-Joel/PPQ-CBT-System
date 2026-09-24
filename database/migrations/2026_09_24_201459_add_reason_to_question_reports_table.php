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
        Schema::table('question_reports', function (Blueprint $table) {
            $table->string('reason', 40)->default('other')->after('attempt_question_id');
            $table->index(['reason', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_reports', function (Blueprint $table) {
            $table->dropIndex(['reason', 'created_at']);
            $table->dropColumn('reason');
        });
    }
};
