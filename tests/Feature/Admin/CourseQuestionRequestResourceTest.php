<?php

namespace Tests\Feature\Admin;

use App\Enums\CourseQuestionRequestStatus;
use App\Filament\Resources\CourseQuestionRequests\CourseQuestionRequestResource;
use App\Filament\Resources\CourseQuestionRequests\Pages\ManageCourseQuestionRequests;
use App\Models\CourseQuestionRequest;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseQuestionRequestResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_review_a_course_question_request_and_leave_a_student_update(): void
    {
        $admin = User::factory()->admin()->create();
        $request = CourseQuestionRequest::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::actingAs($admin)
            ->test(ManageCourseQuestionRequests::class)
            ->assertCanSeeTableRecords([$request])
            ->callAction(TestAction::make('review')->table($request), [
                'status' => CourseQuestionRequestStatus::Added->value,
                'admin_notes' => 'The question bank has now been added. You can purchase it from Available Courses.',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified('Course request updated');

        $request->refresh();
        $this->assertSame(CourseQuestionRequestStatus::Added, $request->status);
        $this->assertSame($admin->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);
        $this->assertSame('The question bank has now been added. You can purchase it from Available Courses.', $request->admin_notes);
    }

    public function test_student_cannot_open_course_request_admin_inbox(): void
    {
        $student = User::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($student)
            ->get(CourseQuestionRequestResource::getUrl('index', panel: 'admin'))
            ->assertForbidden();
    }
}
