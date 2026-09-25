<?php

namespace App\Filament\Resources\Students\Tables;

use App\CourseAccess\GrantCourseAccess;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable(),
                TextColumn::make('course_accesses_count')
                    ->label('Course access')
                    ->counts('courseAccesses')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('level')
                    ->relationship('level', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active status'),
            ])
            ->recordActions([
                Action::make('unlockCourse')
                    ->label('Unlock course')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->modalHeading(fn (User $record): string => "Unlock a course for {$record->name}")
                    ->modalDescription('This grants access immediately without requiring a payment. Use it for testing or an approved manual payment.')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(fn (User $record): array => Course::query()
                                ->active()
                                ->eligibleFor($record)
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Course $course): array => [
                                    $course->getKey() => "{$course->code} — {$course->name}",
                                ])
                                ->all())
                            ->searchable()
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Access expires')
                            ->minDate(now())
                            ->helperText('Optional. Leave blank to unlock the course without an expiry date.'),
                    ])
                    ->action(function (User $record, array $data, GrantCourseAccess $grantAccess): void {
                        $course = Course::query()->findOrFail($data['course_id']);

                        $grantAccess->handle(
                            $record,
                            $course,
                            filled($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
                        );

                        Notification::make()
                            ->title("{$course->code} unlocked for {$record->name}")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
