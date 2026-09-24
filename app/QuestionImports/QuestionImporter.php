<?php

namespace App\QuestionImports;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class QuestionImporter
{
    /** @param list<ParsedQuestion> $questions */
    public function import(Course $course, array $questions): int
    {
        return DB::transaction(function () use ($course, $questions): int {
            foreach ($questions as $parsedQuestion) {
                $question = $course->questions()->create([
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

            return count($questions);
        });
    }
}
