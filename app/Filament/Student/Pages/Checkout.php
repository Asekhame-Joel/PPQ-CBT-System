<?php

namespace App\Filament\Student\Pages;

use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\User;
use App\Payments\InitiateCoursePayment;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use Throwable;

class Checkout extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'checkout/{course}';

    protected string $view = 'filament.student.pages.checkout';

    public Course $course;

    public function mount(Course $course): void
    {
        /** @var User $student */
        $student = auth()->user();

        abort_unless(Course::query()->active()->eligibleFor($student)->whereKey($course)->exists(), 404);
        abort_if($course->questions()->active()->count() < $course->min_question_count, 404);
        abort_if(
            CourseAccess::query()->available()->whereBelongsTo($student)->whereBelongsTo($course)->exists(),
            404,
        );

        $this->course = $course->load('level');
    }

    public function pay(InitiateCoursePayment $initiatePayment): void
    {
        try {
            $initialization = $initiatePayment->handle(auth()->user(), $this->course);
            $this->redirect($initialization->authorizationUrl);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Payment could not be started')
                ->body('Please try again shortly. You have not been charged.')
                ->danger()
                ->send();
        }
    }

    public function getHeading(): string
    {
        return 'Unlock '.$this->course->code;
    }
}
