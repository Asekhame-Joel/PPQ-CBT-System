<?php

namespace App\Filament\Student\Pages;

use App\Enums\CourseQuestionRequestStatus;
use App\Models\CourseQuestionRequest;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class RequestCourseQuestions extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'Request a course';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.student.pages.request-course-questions';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course details')
                    ->description('Tell us the course you want added to the ExamForge question bank.')
                    ->schema([
                        TextInput::make('course_code')
                            ->label('Course code')
                            ->placeholder('For example: GST 111')
                            ->maxLength(30)
                            ->required(),
                        TextInput::make('course_name')
                            ->label('Course title')
                            ->placeholder('For example: Communication in English I')
                            ->maxLength(180)
                            ->required(),
                        Textarea::make('message')
                            ->label('Anything else we should know? (optional)')
                            ->placeholder('You can include your department, level, or the type of past questions you need.')
                            ->rows(5)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function submitRequest(): void
    {
        $data = $this->form->getState();

        CourseQuestionRequest::query()->create([
            ...$data,
            'course_code' => strtoupper(trim($data['course_code'])),
            'user_id' => auth()->id(),
            'status' => CourseQuestionRequestStatus::Requested,
        ]);

        $this->form->fill();

        Notification::make()
            ->title('Course request sent')
            ->body('We have received your request and will update you here once it has been reviewed.')
            ->success()
            ->send();
    }

    /** @return Collection<int, CourseQuestionRequest> */
    #[Computed]
    public function requests(): Collection
    {
        return CourseQuestionRequest::query()
            ->where('user_id', auth()->id())
            ->with('reviewer:id,name')
            ->latest()
            ->limit(10)
            ->get();
    }
}
