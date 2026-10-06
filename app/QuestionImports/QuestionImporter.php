<?php

namespace App\QuestionImports;

use App\Models\Course;
use App\Models\QuestionImportBatch;
use Illuminate\Support\Facades\DB;

class QuestionImporter
{
    /** @param list<ParsedQuestion> $questions */
    public function import(Course $course, array $questions, ?int $importedBy, string $sourceFilename): QuestionImportBatch
    {
        return DB::transaction(function () use ($course, $questions, $importedBy, $sourceFilename): QuestionImportBatch {
            $batch = $course->questionImportBatches()->create([
                'imported_by' => $importedBy,
                'source_filename' => $sourceFilename,
                'source_format' => strtolower(pathinfo($sourceFilename, PATHINFO_EXTENSION)),
                'question_count' => count($questions),
            ]);

            foreach ($questions as $parsedQuestion) {
                $question = $course->questions()->create([
                    'question_import_batch_id' => $batch->id,
                    'question_text' => $parsedQuestion->text,
                    'explanation' => $parsedQuestion->explanation,
                    'is_active' => true,
                ]);

                foreach ($parsedQuestion->options as $index => $parsedOption) {
                    $question->options()->create([
                        'option_text' => $parsedOption->text,
                        'is_correct' => $parsedOption->isCorrect,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            return $batch;
        });
    }
}
