<?php

namespace App\Filament\Resources\CourseQuestionRequests;

use App\Enums\CourseQuestionRequestStatus;
use App\Filament\Resources\CourseQuestionRequests\Pages\ManageCourseQuestionRequests;
use App\Models\CourseQuestionRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseQuestionRequestResource extends Resource
{
    protected static ?string $model = CourseQuestionRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Review';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Course requests';

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
                TextColumn::make('student.email')
                    ->label('Student')
                    ->description(fn (CourseQuestionRequest $record): string => $record->student->name)
                    ->searchable(),
                TextColumn::make('course_code')
                    ->label('Course')
                    ->description(fn (CourseQuestionRequest $record): string => $record->course_name)
                    ->searchable(),
                TextColumn::make('message')
                    ->label('Student note')
                    ->limit(48)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (CourseQuestionRequestStatus $state): string => $state->label())
                    ->color(fn (CourseQuestionRequestStatus $state): string => match ($state) {
                        CourseQuestionRequestStatus::Requested => 'warning',
                        CourseQuestionRequestStatus::InReview => 'info',
                        CourseQuestionRequestStatus::Added => 'success',
                        CourseQuestionRequestStatus::Unavailable => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(CourseQuestionRequestStatus::options()),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->modalHeading('Review course question request')
                    ->modalDescription(fn (CourseQuestionRequest $record): string => "{$record->course_code} — {$record->course_name}\n\nStudent note: ".($record->message ?: 'No additional note provided.'))
                    ->schema([
                        Select::make('status')
                            ->options(CourseQuestionRequestStatus::options())
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('Update for the student')
                            ->helperText('The student can see this update on their course request page.')
                            ->rows(5)
                            ->maxLength(3000)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (CourseQuestionRequest $record): array => [
                        'status' => $record->status->value,
                        'admin_notes' => $record->admin_notes,
                    ])
                    ->action(function (CourseQuestionRequest $record, array $data): void {
                        $record->update([
                            'status' => CourseQuestionRequestStatus::from($data['status']),
                            'admin_notes' => $data['admin_notes'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Course request updated')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No course requests yet')
            ->emptyStateDescription('Students requesting new course question banks will appear here.');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCourseQuestionRequests::route('/'),
        ];
    }
}
