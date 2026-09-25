<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Course;
use App\QuestionImports\ParsedOption;
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

    /** @var list<string> */
    public array $sourceErrors = [];

    /** @var list<string> */
    public array $skippedDuplicates = [];

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
        $result = $this->parseUploadedFile($parser, $data['file']);
        $filtered = $duplicateDetector->filter($course, $result->questions);

        $this->sourceErrors = $result->errors;
        $this->skippedDuplicates = $filtered->skipped;
        $this->setPreview($filtered->questions);
        $this->refreshPreviewErrors($course, $duplicateDetector);
    }

    public function import(
        QuestionImporter $importer,
        QuestionDuplicateDetector $duplicateDetector,
    ): void {
        $course = $this->previewCourse();
        $result = $this->previewResult($course, $duplicateDetector);
        $this->previewErrors = $result->errors;

        if (! $result->isValid() || $result->questions === []) {
            Notification::make()
                ->title('Resolve the preview errors before importing')
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

    public function validatePreview(QuestionDuplicateDetector $duplicateDetector): void
    {
        $result = $this->previewResult($this->previewCourse(), $duplicateDetector);
        $this->previewErrors = $result->errors;

        Notification::make()
            ->title($result->isValid() ? 'Preview is ready to import' : 'Some questions still need attention')
            ->color($result->isValid() ? 'success' : 'danger')
            ->send();
    }

    public function removePreviewQuestion(int $index, QuestionDuplicateDetector $duplicateDetector): void
    {
        if (! array_key_exists($index, $this->previewQuestions)) {
            return;
        }

        unset($this->previewQuestions[$index]);
        $this->previewQuestions = array_values($this->previewQuestions);
        $this->refreshPreviewErrors($this->previewCourse(), $duplicateDetector);
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

    /** @param list<ParsedQuestion> $questions */
    private function setPreview(array $questions): void
    {
        $this->previewQuestions = array_map(fn (ParsedQuestion $question): array => [
            'text' => $question->text,
            'explanation' => $question->explanation,
            'correct_label' => collect($question->options)->first(
                fn (ParsedOption $option): bool => $option->isCorrect,
            )?->label,
            'options' => array_map(fn ($option): array => [
                'label' => $option->label,
                'text' => $option->text,
            ], $question->options),
        ], $questions);
        $this->hasPreview = true;
    }

    private function previewCourse(): Course
    {
        return Course::active()->findOrFail(data_get($this->data, 'course_id'));
    }

    private function refreshPreviewErrors(Course $course, QuestionDuplicateDetector $duplicateDetector): void
    {
        $this->previewErrors = $this->previewResult($course, $duplicateDetector)->errors;
    }

    private function previewResult(Course $course, QuestionDuplicateDetector $duplicateDetector): QuestionImportResult
    {
        $questions = [];
        $errors = $this->sourceErrors;

        foreach ($this->previewQuestions as $index => $question) {
            $number = $index + 1;
            $text = trim((string) ($question['text'] ?? ''));
            $explanation = trim((string) ($question['explanation'] ?? ''));
            $correctLabel = strtoupper(trim((string) ($question['correct_label'] ?? '')));
            $options = [];
            $labels = [];

            if ($text === '') {
                $errors[] = "Question {$number}: the question text is required.";
            }

            foreach (($question['options'] ?? []) as $option) {
                $label = strtoupper(trim((string) ($option['label'] ?? '')));
                $optionText = trim((string) ($option['text'] ?? ''));

                if ($label === '' || $optionText === '') {
                    $errors[] = "Question {$number}: every answer choice must have text.";

                    continue;
                }

                $labels[] = $label;
                $options[] = new ParsedOption($label, $optionText, $label === $correctLabel);
            }

            if (count($options) < 2) {
                $errors[] = "Question {$number}: at least two answer choices are required.";
            }

            if ($correctLabel === '' || ! in_array($correctLabel, $labels, true)) {
                $errors[] = "Question {$number}: select the correct answer.";
            }

            $questions[] = new ParsedQuestion(
                $text,
                $options,
                $explanation !== '' ? $explanation : null,
            );
        }

        if ($questions !== []) {
            $errors = [...$errors, ...$duplicateDetector->errors($course, $questions)];
        }

        return new QuestionImportResult($questions, array_values(array_unique($errors)));
    }
}
