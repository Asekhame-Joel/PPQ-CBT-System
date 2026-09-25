<?php

namespace App\Filament\Student\Pages;

use App\Models\Course;
use App\Models\CourseAccess;
use App\Practice\StartPracticeAttempt;
use App\Support\StudentActionRateLimiter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PracticeSetup extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'practice/{course}';

    protected string $view = 'filament.student.pages.practice-setup';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public Course $course;

    public int $availableQuestionCount;

    public function mount(Course $course): void
    {
        abort_unless($course->is_active, 404);
        abort_unless(
            CourseAccess::query()
                ->available()
                ->where('user_id', auth()->id())
                ->where('course_id', $course->id)
                ->exists(),
            404,
        );

        $this->course = $course;
        $this->availableQuestionCount = $course->questions()->active()->count();

        abort_if($this->availableQuestionCount < $course->min_question_count, 404);

        $settings = auth()->user()->studentSetting;
        $maximumQuestions = min($course->max_question_count, $this->availableQuestionCount);

        $this->form->fill([
            'question_count' => $this->boundedValue(
                $settings?->preferred_question_count ?? $course->default_question_count,
                $course->min_question_count,
                $maximumQuestions,
            ),
            'duration_minutes' => $this->boundedValue(
                $settings?->preferred_duration ?? $course->default_duration,
                $course->min_duration,
                $course->max_duration,
            ),
            'randomize_questions' => $settings?->randomize_questions ?? true,
            'randomize_options' => $settings?->randomize_options ?? true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Practice settings')
                    ->description('Choose how many questions to answer and how long the practice should last.')
                    ->schema([
                        TextInput::make('question_count')
                            ->label('Number of questions')
                            ->numeric()
                            ->integer()
                            ->minValue(fn (): int => $this->course->min_question_count)
                            ->maxValue(fn (): int => min($this->course->max_question_count, $this->availableQuestionCount))
                            ->helperText(fn (): string => "Choose {$this->course->min_question_count} to ".min($this->course->max_question_count, $this->availableQuestionCount).' questions.')
                            ->required(),
                        TextInput::make('duration_minutes')
                            ->label('Duration in minutes')
                            ->numeric()
                            ->integer()
                            ->minValue(fn (): int => $this->course->min_duration)
                            ->maxValue(fn (): int => $this->course->max_duration)
                            ->helperText(fn (): string => "Choose {$this->course->min_duration} to {$this->course->max_duration} minutes.")
                            ->required(),
                        Toggle::make('randomize_questions')
                            ->label('Randomize question order')
                            ->default(true),
                        Toggle::make('randomize_options')
                            ->label('Randomize answer options')
                            ->default(true),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        auth()->user()->studentSetting()->updateOrCreate([], [
            'preferred_question_count' => $data['question_count'],
            'preferred_duration' => $data['duration_minutes'],
            'randomize_questions' => $data['randomize_questions'],
            'randomize_options' => $data['randomize_options'],
        ]);

        Notification::make()
            ->title('Practice settings saved')
            ->success()
            ->send();
    }

    public function startPractice(StartPracticeAttempt $startAttempt, StudentActionRateLimiter $rateLimiter): void
    {
        $data = $this->form->getState();
        $rateLimiter->ensure(auth()->user(), 'start-practice', maximumAttempts: 5, decaySeconds: 60);

        auth()->user()->studentSetting()->updateOrCreate([], [
            'preferred_question_count' => $data['question_count'],
            'preferred_duration' => $data['duration_minutes'],
            'randomize_questions' => $data['randomize_questions'],
            'randomize_options' => $data['randomize_options'],
        ]);

        $attempt = $startAttempt->handle(
            auth()->user(),
            $this->course,
            $data['question_count'],
            $data['duration_minutes'],
            $data['randomize_questions'],
            $data['randomize_options'],
        );

        $this->redirect(PracticeSession::getUrl(['attempt' => $attempt], panel: 'student'));
    }

    public function getHeading(): string
    {
        return "Practice {$this->course->code}";
    }

    private function boundedValue(int $value, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, $value));
    }
}
