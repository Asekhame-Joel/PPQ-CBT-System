<?php

namespace App\Filament\Resources\QuestionImportBatches;

use App\Filament\Resources\QuestionImportBatches\Pages\ManageQuestionImportBatches;
use App\Models\QuestionImportBatch;
use App\QuestionImports\DeleteQuestionImportBatch;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionImportBatchResource extends Resource
{
    protected static ?string $model = QuestionImportBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Core operations';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Question upload batches';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.code')
                    ->label('Course')
                    ->description(fn (QuestionImportBatch $record): string => $record->course->name)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('source_filename')
                    ->label('File')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('source_format')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('questions_count')
                    ->label('Live questions')
                    ->counts('questions')
                    ->sortable(),
                TextColumn::make('question_count')
                    ->label('Imported')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('importer.email')
                    ->label('Imported by')
                    ->placeholder('Deleted administrator')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->relationship('course', 'name'),
            ])
            ->recordActions([
                Action::make('deleteBatch')
                    ->label('Delete batch')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete this question batch?')
                    ->modalDescription('This removes every live question imported in this batch. Existing and in-progress practice attempts retain their saved questions, options, answers, and explanations.')
                    ->modalSubmitActionLabel('Delete batch')
                    ->schema([
                        TextInput::make('confirmation')
                            ->label('Type DELETE to confirm')
                            ->required()
                            ->rules(['in:DELETE']),
                    ])
                    ->action(function (QuestionImportBatch $record, DeleteQuestionImportBatch $deleteBatch): void {
                        $deletedCount = $deleteBatch->handle($record);

                        Notification::make()
                            ->title("{$deletedCount} live question(s) deleted")
                            ->body('Past practice attempts still retain their saved questions, options, answers, and explanations.')
                            ->success()
                            ->send();
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['course:id,code,name', 'importer:id,email'])
                ->withCount('questions'))
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No uploaded question batches yet')
            ->emptyStateDescription('New DOCX and TXT imports will appear here and can be removed together.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuestionImportBatches::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
