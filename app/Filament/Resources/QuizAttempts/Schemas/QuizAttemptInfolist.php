<?php

namespace App\Filament\Resources\QuizAttempts\Schemas;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuizAttemptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attempt overview')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Student'),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('course.code')
                            ->label('Course code'),
                        TextEntry::make('course.name')
                            ->label('Course'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (AttemptStatus $state): string => str($state->value)->replace('_', ' ')->title()),
                        TextEntry::make('attempt_type')
                            ->label('Attempt type')
                            ->formatStateUsing(fn (AttemptType $state): string => str($state->value)->replace('_', ' ')->title()),
                    ])
                    ->columns(3),
                Section::make('Result')
                    ->schema([
                        TextEntry::make('score_percentage')
                            ->label('Score')
                            ->suffix('%')
                            ->placeholder('Not scored'),
                        TextEntry::make('correct_count')
                            ->label('Correct')
                            ->numeric()
                            ->placeholder('—'),
                        TextEntry::make('incorrect_count')
                            ->label('Incorrect')
                            ->numeric()
                            ->placeholder('—'),
                        TextEntry::make('unanswered_count')
                            ->label('Unanswered')
                            ->numeric()
                            ->placeholder('—'),
                        TextEntry::make('question_count')
                            ->label('Questions')
                            ->numeric(),
                    ])
                    ->columns(5),
                Section::make('Timing')
                    ->schema([
                        TextEntry::make('started_at')->dateTime(),
                        TextEntry::make('expires_at')->dateTime(),
                        TextEntry::make('submitted_at')->dateTime()->placeholder('Not submitted'),
                        TextEntry::make('duration_minutes')->label('Duration')->suffix(' minutes'),
                    ])
                    ->columns(4),
            ]);
    }
}
