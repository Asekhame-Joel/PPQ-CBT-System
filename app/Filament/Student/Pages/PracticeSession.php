<?php

namespace App\Filament\Student\Pages;

use App\Enums\AttemptStatus;
use App\Models\AttemptQuestion;
use App\Models\QuizAttempt;
use App\Practice\SaveAttemptAnswer;
use App\Practice\SubmitPracticeAttempt;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class PracticeSession extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'attempts/{attempt}';

    protected string $view = 'filament.student.pages.practice-session';

    public int $attemptId;

    public int $currentPosition = 1;

    public int $questionCount;

    public string $courseCode;

    public int $expiresAtTimestamp;

    public function mount(QuizAttempt $attempt, SubmitPracticeAttempt $submitAttempt): void
    {
        abort_unless($attempt->user_id === auth()->id(), 404);
        abort_unless($attempt->status === AttemptStatus::InProgress, 404);

        if ($attempt->expires_at->lessThanOrEqualTo(now())) {
            $result = $submitAttempt->handle(auth()->user(), $attempt->id, requireExpired: true);
            $this->redirect(PracticeResult::getUrl(['attempt' => $result], panel: 'student'));

            return;
        }

        $this->attemptId = $attempt->id;
        $this->questionCount = $attempt->question_count;
        $this->courseCode = $attempt->course->code;
        $this->expiresAtTimestamp = $attempt->expires_at->getTimestamp();
    }

    #[Computed]
    public function currentQuestion(): AttemptQuestion
    {
        return AttemptQuestion::query()
            ->select(['id', 'quiz_attempt_id', 'position', 'question_snapshot', 'options_snapshot'])
            ->where('quiz_attempt_id', $this->attemptId)
            ->where('position', $this->currentPosition)
            ->with('answer:id,attempt_question_id,selected_option_id')
            ->sole();
    }

    /** @return Collection<int, AttemptQuestion> */
    #[Computed]
    public function questionStates(): Collection
    {
        return AttemptQuestion::query()
            ->select(['id', 'quiz_attempt_id', 'position'])
            ->where('quiz_attempt_id', $this->attemptId)
            ->with('answer:id,attempt_question_id,selected_option_id')
            ->orderBy('position')
            ->get();
    }

    public function selectAnswer(int $attemptQuestionId, int $selectedOptionId, SaveAttemptAnswer $saveAnswer): void
    {
        $saveAnswer->handle(
            auth()->user(),
            $this->attemptId,
            $attemptQuestionId,
            $selectedOptionId,
        );

        unset($this->currentQuestion, $this->questionStates);
    }

    public function goTo(int $position): void
    {
        if ($position < 1 || $position > $this->questionCount) {
            return;
        }

        $this->currentPosition = $position;
        unset($this->currentQuestion);
    }

    public function previousQuestion(): void
    {
        $this->goTo($this->currentPosition - 1);
    }

    public function nextQuestion(): void
    {
        $this->goTo($this->currentPosition + 1);
    }

    public function submitAttempt(SubmitPracticeAttempt $submitAttempt): void
    {
        $result = $submitAttempt->handle(auth()->user(), $this->attemptId);

        Notification::make()
            ->title('Practice submitted')
            ->success()
            ->send();

        $this->redirect(PracticeResult::getUrl(['attempt' => $result], panel: 'student'));
    }

    public function expireAttempt(SubmitPracticeAttempt $submitAttempt): void
    {
        $result = $submitAttempt->handle(auth()->user(), $this->attemptId, requireExpired: true);

        Notification::make()
            ->title('Time is up — your answers were submitted')
            ->warning()
            ->send();

        $this->redirect(PracticeResult::getUrl(['attempt' => $result], panel: 'student'));
    }

    public function getHeading(): string
    {
        return "{$this->courseCode} Practice";
    }
}
