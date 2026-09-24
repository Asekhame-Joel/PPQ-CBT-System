<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Question;
use App\QuestionImports\ParsedQuestion;
use App\QuestionImports\QuestionDuplicateDetector;
use App\QuestionImports\QuestionFingerprint;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QuestionImportDuplicateTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_fingerprint_ignores_capitalization_and_extra_spacing(): void
    {
        $this->assertSame(
            QuestionFingerprint::make('  What   is PHP? '),
            QuestionFingerprint::make('what is php?'),
        );
    }

    public function test_it_detects_existing_questions_only_in_the_selected_course(): void
    {
        $course = Course::factory()->create(['code' => 'CSC101']);
        $otherCourse = Course::factory()->create();
        Question::factory()->for($course)->create(['question_text' => 'What is PHP?']);
        $questions = [new ParsedQuestion('  WHAT   IS php?  ', [])];
        $detector = new QuestionDuplicateDetector;

        $this->assertSame(
            ['Question 1: this question already exists in CSC101.'],
            $detector->errors($course, $questions),
        );
        $this->assertSame([], $detector->errors($otherCourse, $questions));
    }

    public function test_it_detects_duplicates_inside_the_same_file(): void
    {
        $course = Course::factory()->create();
        $questions = [
            new ParsedQuestion('What is Laravel?', []),
            new ParsedQuestion('Another question?', []),
            new ParsedQuestion(' what  is LARAVEL? ', []),
        ];

        $errors = (new QuestionDuplicateDetector)->errors($course, $questions);

        $this->assertSame(['Question 3: duplicates question 1 in this file.'], $errors);
    }

    public function test_database_constraint_prevents_duplicates_in_the_same_course(): void
    {
        $course = Course::factory()->create();
        Question::factory()->for($course)->create(['question_text' => 'Duplicate me']);

        $this->expectException(QueryException::class);

        Question::factory()->for($course)->create(['question_text' => ' duplicate   ME ']);
    }
}
