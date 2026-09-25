<?php

namespace App\QuestionImports;

use App\Models\Course;

class QuestionDuplicateDetector
{
    /** @param list<ParsedQuestion> $questions */
    public function filter(Course $course, array $questions): QuestionDuplicateFilterResult
    {
        $hashes = array_map(
            fn (ParsedQuestion $question): string => QuestionFingerprint::make($question->text),
            $questions,
        );
        $existingHashes = $course->questions()
            ->whereIn('content_hash', array_unique($hashes))
            ->pluck('content_hash')
            ->filter()
            ->flip();
        $seen = [];
        $uniqueQuestions = [];
        $skipped = [];

        foreach ($questions as $index => $question) {
            $hash = $hashes[$index];
            $questionNumber = $index + 1;

            if ($existingHashes->has($hash)) {
                $skipped[] = "Question {$questionNumber} was skipped because it already exists in {$course->code}.";

                continue;
            }

            if (isset($seen[$hash])) {
                $skipped[] = "Question {$questionNumber} was skipped because it duplicates question {$seen[$hash]} in this file.";

                continue;
            }

            $seen[$hash] = $questionNumber;
            $uniqueQuestions[] = $question;
        }

        return new QuestionDuplicateFilterResult($uniqueQuestions, $skipped);
    }

    /**
     * @param  list<ParsedQuestion>  $questions
     * @return list<string>
     */
    public function errors(Course $course, array $questions): array
    {
        $hashes = array_map(
            fn (ParsedQuestion $question): string => QuestionFingerprint::make($question->text),
            $questions,
        );
        $existingHashes = $course->questions()
            ->whereIn('content_hash', array_unique($hashes))
            ->pluck('content_hash')
            ->filter()
            ->flip();
        $seen = [];
        $errors = [];

        foreach ($hashes as $index => $hash) {
            $questionNumber = $index + 1;

            if ($existingHashes->has($hash)) {
                $errors[] = "Question {$questionNumber}: this question already exists in {$course->code}.";

                continue;
            }

            if (isset($seen[$hash])) {
                $errors[] = "Question {$questionNumber}: duplicates question {$seen[$hash]} in this file.";

                continue;
            }

            $seen[$hash] = $questionNumber;
        }

        return $errors;
    }
}
