<?php

namespace App\Console\Commands;

use App\Enums\AccessSource;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Department;
use App\Models\Level;
use App\Practice\PracticeQuestionSelector;
use App\QuestionImports\QuestionFingerprint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedLoadTestData extends Command
{
    protected $signature = 'exam:seed-load-test
        {--students=500 : Number of load-test student accounts to create}
        {--questions=5000 : Total number of questions in the load-test course}';

    protected $description = 'Create a local-only ExamForge dataset for capacity testing';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Load-test data cannot be created in production.');

            return self::FAILURE;
        }

        $studentCount = (int) $this->option('students');
        $questionCount = (int) $this->option('questions');

        if ($studentCount < 1 || $questionCount < 2) {
            $this->error('Use at least 1 student and 2 questions.');

            return self::FAILURE;
        }

        [$department, $level, $course] = $this->loadTestCourse();
        $this->createQuestions($course, $questionCount);
        $this->createStudentsAndAccess($department, $level, $course, $studentCount);
        PracticeQuestionSelector::forget($course);

        $this->info("Load-test data is ready: {$questionCount} questions and {$studentCount} unlocked students.");
        $this->line('Student email pattern: loadtest+1@example.test through loadtest+'.($studentCount).'@example.test');
        $this->line('Student password: load-test-password');

        return self::SUCCESS;
    }

    /** @return array{Department, Level, Course} */
    private function loadTestCourse(): array
    {
        $department = Department::query()->firstOrCreate(
            ['code' => 'LOAD'],
            ['name' => 'Load Testing Department', 'is_active' => true],
        );
        $level = Level::query()->firstOrCreate(
            ['name' => 'Load Test Level'],
            ['sort_order' => 999, 'is_active' => true],
        );
        $course = Course::query()->firstOrCreate(
            ['code' => 'LOAD101'],
            [
                'level_id' => $level->id,
                'name' => 'ExamForge Load Test Course',
                'description' => 'Local capacity-test data only.',
                'price' => 0,
                'default_question_count' => 100,
                'default_duration' => 60,
                'min_question_count' => 2,
                'max_question_count' => 100,
                'min_duration' => 1,
                'max_duration' => 120,
                'is_active' => true,
            ],
        );
        $course->departments()->syncWithoutDetaching([$department->id]);

        return [$department, $level, $course];
    }

    private function createQuestions(Course $course, int $targetCount): void
    {
        $existingCount = $course->questions()->count();

        if ($existingCount >= $targetCount) {
            return;
        }

        $now = now();

        foreach (array_chunk(range($existingCount + 1, $targetCount), 250) as $numbers) {
            $questions = array_map(function (int $number) use ($course, $now): array {
                $text = "Load test question {$number}: which option is correct?";

                return [
                    'course_id' => $course->id,
                    'question_text' => $text,
                    'content_hash' => QuestionFingerprint::make($text),
                    'explanation' => 'Option B is the correct answer for this load-test question.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $numbers);
            DB::table('questions')->insert($questions);

            $questionIds = DB::table('questions')
                ->where('course_id', $course->id)
                ->whereIn('content_hash', array_column($questions, 'content_hash'))
                ->pluck('id', 'content_hash');
            $options = [];

            foreach ($questions as $question) {
                $questionId = $questionIds[QuestionFingerprint::make($question['question_text'])];

                foreach (['A', 'B', 'C', 'D'] as $offset => $label) {
                    $options[] = [
                        'question_id' => $questionId,
                        'option_text' => "Option {$label}",
                        'is_correct' => $label === 'B',
                        'sort_order' => $offset + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('question_options')->insert($options);
        }
    }

    private function createStudentsAndAccess(Department $department, Level $level, Course $course, int $studentCount): void
    {
        $now = now();
        $password = Hash::make('load-test-password');

        foreach (array_chunk(range(1, $studentCount), 250) as $numbers) {
            $students = array_map(fn (int $number): array => [
                'name' => "Load Test Student {$number}",
                'email' => "loadtest+{$number}@example.test",
                'phone' => '080'.str_pad((string) $number, 8, '0', STR_PAD_LEFT),
                'role' => UserRole::Student->value,
                'department_id' => $department->id,
                'level_id' => $level->id,
                'is_active' => true,
                'email_verified_at' => $now,
                'password' => $password,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $numbers);
            DB::table('users')->upsert(
                $students,
                ['email'],
                ['name', 'phone', 'role', 'department_id', 'level_id', 'is_active', 'email_verified_at', 'password', 'updated_at'],
            );
            $studentIds = DB::table('users')
                ->whereIn('email', array_column($students, 'email'))
                ->pluck('id');
            DB::table('course_access')->upsert(
                $studentIds->map(fn (int $studentId): array => [
                    'user_id' => $studentId,
                    'course_id' => $course->id,
                    'payment_id' => null,
                    'access_source' => AccessSource::Admin->value,
                    'granted_at' => $now,
                    'expires_at' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all(),
                ['user_id', 'course_id'],
                ['access_source', 'granted_at', 'expires_at', 'is_active', 'updated_at'],
            );
        }
    }
}
