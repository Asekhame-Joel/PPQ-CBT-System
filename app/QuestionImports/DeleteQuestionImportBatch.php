<?php

namespace App\QuestionImports;

use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Practice\PracticeQuestionSelector;
use Illuminate\Support\Facades\DB;

class DeleteQuestionImportBatch
{
    public function handle(QuestionImportBatch $batch): int
    {
        $courseId = $batch->course_id;

        $deletedCount = DB::transaction(function () use ($batch): int {
            $deletedCount = Question::query()
                ->whereBelongsTo($batch, 'importBatch')
                ->delete();

            $batch->delete();

            return $deletedCount;
        });

        PracticeQuestionSelector::forget($courseId);

        return $deletedCount;
    }
}
