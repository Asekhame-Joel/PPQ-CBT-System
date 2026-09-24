<?php

namespace App\Filament\Resources\QuestionReports\Tables;

use App\Enums\QuestionReportReason;
use App\Enums\QuestionReportStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter.email')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('attemptQuestion.quizAttempt.course.code')
                    ->label('Course'),
                TextColumn::make('attemptQuestion.question_snapshot')
                    ->label('Question')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('reason')
                    ->formatStateUsing(fn (QuestionReportReason $state): string => $state->label())
                    ->badge(),
                TextColumn::make('status')
                    ->formatStateUsing(fn (QuestionReportStatus $state): string => ucfirst($state->value))
                    ->badge()
                    ->color(fn (QuestionReportStatus $state): string => match ($state) {
                        QuestionReportStatus::Pending => 'warning',
                        QuestionReportStatus::Reviewed => 'info',
                        QuestionReportStatus::Resolved => 'success',
                        QuestionReportStatus::Dismissed => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->options(QuestionReportReason::options()),
                SelectFilter::make('status')
                    ->options([
                        QuestionReportStatus::Pending->value => 'Pending',
                        QuestionReportStatus::Reviewed->value => 'Reviewed',
                        QuestionReportStatus::Resolved->value => 'Resolved',
                        QuestionReportStatus::Dismissed->value => 'Dismissed',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
