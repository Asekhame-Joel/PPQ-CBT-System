<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.code')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('options_count')
                    ->counts('options')
                    ->label('Options')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('course')
                    ->relationship('course', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Active status'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
