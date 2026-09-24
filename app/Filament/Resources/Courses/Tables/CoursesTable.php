<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level.name')
                    ->label('Level')
                    ->sortable(),
                TextColumn::make('departments.name')
                    ->label('Departments')
                    ->badge()
                    ->limitList(3),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('default_question_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('default_duration')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_question_count')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_question_count')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_duration')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('max_duration')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('level')
                    ->relationship('level', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Active status'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('code');
    }
}
