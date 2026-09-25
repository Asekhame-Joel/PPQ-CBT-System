<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Course;
use App\QuestionImports\ParsedQuestion;
use App\QuestionImports\QuestionDuplicateDetector;
use App\QuestionImports\QuestionFileParser;
use App\QuestionImports\QuestionImporter;
use App\QuestionImports\QuestionImportResult;
use App\QuestionImports\QuestionImportTemplate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportQuestions extends Page
{
    protected static string $resource = QuestionResource::class;

    protected string $view = 'filament.resources.questions.pages.import-questions';

    /** @var array<string, mixed> | null */
    public ?array $data = [];

    /** @var list<array<string, mixed>> */
    public array $previewQuestions = [];

    /** @var list<string> */
    public array $previewErrors = [];

    public bool $hasPreview = false;

    public function getHeading(): string
    {
        return 'Import questions';
    }

    public function getSubheading(): string
    {
        return 'Upload a DOCX or TXT file, review every question, then confirm the import.';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question file')
                    ->description('Upload Moodle Aiken text or a structured Word table file. You can review everything before importing.')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(fn (): array => Course::active()
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Course $course): array => [
                                    $course->id => "{$course->code} — {$course->name}",
                                ])
                                ->all())
                            ->rules([
                                Rule::exists('courses', 'id')->where('is_active', true),
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                        FileUpload::make('file')
                            ->label('Questions file')
                            ->hint('DOCX or TXT')
                            ->acceptedFileTypes([
                                'text/plain',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->helperText('Accepted formats: UTF-8 .txt (Aiken) or structured .docx. Maximum size: 10 MB.')
                            ->maxSize(10240)
                            ->storeFiles(false)
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function preview(QuestionFileParser $parser, QuestionDuplicateDetector $duplicateDetector): void
    {
        $data = $this->form->getState();
        $course = Course::active()->findOrFail($data['course_id']);
        $result = $this->withDuplicateErrors(
            $this->parseUploadedFile($parser, $data['file']),
            $course,
            $duplicateDetector,
        );

        $this->setPreview($result->questions, $result->errors);
    }

    public function import(
        QuestionFileParser $parser,
        QuestionImporter $importer,
        QuestionDuplicateDetector $duplicateDetector,
    ): void {
        $data = $this->form->getState();
        $course = Course::active()->findOrFail($data['course_id']);
        $result = $this->withDuplicateErrors(
            $this->parseUploadedFile($parser, $data['file']),
            $course,
            $duplicateDetector,
        );
        $this->setPreview($result->questions, $result->errors);

        if (! $result->isValid() || $result->questions === []) {
            Notification::make()
                ->title('Resolve the file errors before importing')
                ->danger()
                ->send();

            return;
        }

        $count = $importer->import($course, $result->questions);

        Notification::make()
            ->title("{$count} questions imported")
            ->success()
            ->send();

        $this->redirect(QuestionResource::getUrl('index'));
    }

    public function downloadTextTemplate(QuestionImportTemplate $template): StreamedResponse
    {
        return response()->streamDownload(
            static fn () => print $template->aikenText(),
            'question-import-template.txt',
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function downloadWordTemplate(QuestionImportTemplate $template): StreamedResponse
    {
        return response()->streamDownload(
            static fn () => print $template->wordDocument(),
            'question-import-template.docx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        );
    }

    private function parseUploadedFile(QuestionFileParser $parser, mixed $file): QuestionImportResult
    {
        if (! $file instanceof UploadedFile) {
            return new QuestionImportResult(errors: ['The uploaded file is no longer available. Please select it again.']);
        }

        return $parser->parse($file->getRealPath(), $file->getClientOriginalName());
    }

    private function withDuplicateErrors(
        QuestionImportResult $result,
        Course $course,
        QuestionDuplicateDetector $duplicateDetector,
    ): QuestionImportResult {
        if ($result->questions === []) {
            return $result;
        }

        return new QuestionImportResult(
            $result->questions,
            [...$result->errors, ...$duplicateDetector->errors($course, $result->questions)],
        );
    }

    /**
     * @param  list<ParsedQuestion>  $questions
     * @param  list<string>  $errors
     */
    private function setPreview(array $questions, array $errors): void
    {
        $this->previewQuestions = array_map(fn (ParsedQuestion $question): array => [
            'text' => $question->text,
            'explanation' => $question->explanation,
            'options' => array_map(fn ($option): array => [
                'label' => $option->label,
                'text' => $option->text,
                'is_correct' => $option->isCorrect,
            ], $question->options),
        ], $questions);
        $this->previewErrors = $errors;
        $this->hasPreview = true;
    }
}
