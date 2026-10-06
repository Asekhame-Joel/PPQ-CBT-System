<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Models\Question;
use App\Practice\PracticeQuestionSelector;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import questions')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->url(QuestionResource::getUrl('import')),
            CreateAction::make(),
            Action::make('deleteAllQuestions')
                ->label('Delete all filtered')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete all filtered questions?')
                ->modalDescription('This permanently deletes every live question matching the current search and filters. Existing and in-progress practice attempts keep their saved question and answer snapshots.')
                ->modalSubmitActionLabel('Delete questions')
                ->schema([
                    TextInput::make('confirmation')
                        ->label('Type DELETE to confirm')
                        ->required()
                        ->rules(['in:DELETE']),
                ])
                ->action(function (): void {
                    $filteredQuestionQuery = $this->getFilteredTableQuery();

                    if ($filteredQuestionQuery === null) {
                        return;
                    }

                    $questionIds = (clone $filteredQuestionQuery)
                        ->reorder()
                        ->pluck('questions.id');

                    if ($questionIds->isEmpty()) {
                        Notification::make()
                            ->title('No questions to delete')
                            ->warning()
                            ->send();

                        return;
                    }

                    $courseIds = Question::query()
                        ->whereKey($questionIds->all())
                        ->pluck('course_id')
                        ->unique();

                    $deletedCount = Question::query()
                        ->whereKey($questionIds->all())
                        ->delete();

                    $courseIds->each(function (int $courseId): void {
                        PracticeQuestionSelector::forget($courseId);
                    });

                    $this->deselectAllTableRecords();

                    Notification::make()
                        ->title("{$deletedCount} question(s) deleted")
                        ->body('Past practice attempts still retain their saved questions, options, answers, and explanations.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
