<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->char('content_hash', 64)->nullable()->after('question_text');
        });

        $seen = [];

        DB::table('questions')
            ->select(['id', 'course_id', 'question_text'])
            ->orderBy('id')
            ->chunkById(500, function ($questions) use (&$seen): void {
                foreach ($questions as $question) {
                    $hash = hash('sha256', Str::squish(Str::lower($question->question_text)));
                    $key = "{$question->course_id}:{$hash}";

                    if (isset($seen[$key])) {
                        continue;
                    }

                    DB::table('questions')
                        ->where('id', $question->id)
                        ->update(['content_hash' => $hash]);
                    $seen[$key] = true;
                }
            });

        Schema::table('questions', function (Blueprint $table) {
            $table->unique(['course_id', 'content_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'content_hash']);
            $table->dropColumn('content_hash');
        });
    }
};
