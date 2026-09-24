<?php

namespace App\Filament\Resources\QuestionReports\Schemas;

use App\Enums\QuestionReportReason;
use App\Enums\QuestionReportStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student report')
                    ->schema([
                        Select::make('reason')
                            ->options(QuestionReportReason::options())
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('report_text')
                            ->label('Student comment')
                            ->rows(5)
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Moderation')
                    ->schema([
                        Select::make('status')
                            ->options([
                                QuestionReportStatus::Pending->value => 'Pending',
                                QuestionReportStatus::Reviewed->value => 'Reviewed',
                                QuestionReportStatus::Resolved->value => 'Resolved',
                                QuestionReportStatus::Dismissed->value => 'Dismissed',
                            ])
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('Admin notes')
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
