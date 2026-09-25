<?php

namespace App\Filament\Resources\QuizAttempts\RelationManagers;

use App\Models\AttemptQuestion;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('position')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('question_snapshot')
                    ->label('Question')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('selected_answer')
                    ->label('Selected answer')
                    ->state(fn (AttemptQuestion $record): ?string => $record->optionText($record->answer?->selected_option_id))
                    ->placeholder('Unanswered')
                    ->wrap(),
                TextColumn::make('correct_answer')
                    ->label('Correct answer')
                    ->state(fn (AttemptQuestion $record): ?string => $record->optionText($record->correct_option_snapshot))
                    ->wrap(),
                IconColumn::make('answer.is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->placeholder('—'),
                TextColumn::make('answer.answered_at')
                    ->label('Answered at')
                    ->dateTime()
                    ->placeholder('Unanswered')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('position');
    }
}
