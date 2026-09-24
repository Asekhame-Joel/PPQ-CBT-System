<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\Course;
use App\Rules\ExactlyOneCorrectOption;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(fn (): array => Course::active()
                                ->orderBy('code')
                                ->get()
                                ->mapWithKeys(fn (Course $course): array => [
                                    $course->id => "{$course->code} — {$course->name}",
                                ])
                                ->all())
                            ->rules([
                                Rule::exists('courses', 'id')->where('is_active', true),
                            ])
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('question_text')
                            ->label('Question text')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('explanation')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columnSpanFull(),
                Section::make('Answer options')
                    ->description('Add at least two options and mark exactly one as correct.')
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Textarea::make('option_text')
                                    ->label('Option')
                                    ->rows(2)
                                    ->required(),
                                Toggle::make('is_correct')
                                    ->label('Correct answer')
                                    ->default(false),
                            ])
                            ->defaultItems(4)
                            ->minItems(2)
                            ->orderColumn('sort_order')
                            ->rules([new ExactlyOneCorrectOption])
                            ->columns(2)
                            ->required(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
