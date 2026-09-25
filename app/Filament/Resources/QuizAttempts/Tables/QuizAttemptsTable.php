<?php

namespace App\Filament\Resources\QuizAttempts\Tables;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->description(fn ($record): string => $record->user->email)
                    ->searchable(),
                TextColumn::make('course.code')
                    ->label('Course')
                    ->description(fn ($record): string => $record->course->name)
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AttemptStatus $state): string => str($state->value)->replace('_', ' ')->title())
                    ->color(fn (AttemptStatus $state): string => match ($state) {
                        AttemptStatus::Submitted => 'success',
                        AttemptStatus::InProgress => 'warning',
                        AttemptStatus::Expired => 'gray',
                    }),
                TextColumn::make('attempt_type')
                    ->label('Type')
                    ->formatStateUsing(fn (AttemptType $state): string => str($state->value)->replace('_', ' ')->title())
                    ->toggleable(),
                TextColumn::make('score_percentage')
                    ->label('Score')
                    ->suffix('%')
                    ->placeholder('Not scored')
                    ->sortable(),
                TextColumn::make('question_count')
                    ->label('Questions')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->placeholder('Not submitted')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        AttemptStatus::InProgress->value => 'In progress',
                        AttemptStatus::Submitted->value => 'Submitted',
                        AttemptStatus::Expired->value => 'Expired',
                    ]),
                SelectFilter::make('course')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user')
                    ->label('Student')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('started_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
