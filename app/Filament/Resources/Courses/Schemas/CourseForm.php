<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Level;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Course details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(30)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(200),
                                Select::make('level_id')
                                    ->label('Level')
                                    ->options(fn (): array => Level::active()
                                        ->orderBy('sort_order')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->rules([
                                        Rule::exists('levels', 'id')->where('is_active', true),
                                    ])
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('departments')
                                    ->relationship(
                                        name: 'departments',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn (Builder $query): Builder => $query
                                            ->where('is_active', true)
                                            ->orderBy('name'),
                                    )
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₦'),
                                Toggle::make('is_active')
                                    ->default(true)
                                    ->required(),
                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Practice settings')
                    ->description('Set the allowed question and duration ranges for this course.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_question_count')
                                    ->label('Minimum questions')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535),
                                TextInput::make('default_question_count')
                                    ->label('Default questions')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->gte('min_question_count')
                                    ->lte('max_question_count'),
                                TextInput::make('max_question_count')
                                    ->label('Maximum questions')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->gte('min_question_count'),
                                TextInput::make('min_duration')
                                    ->label('Minimum duration (minutes)')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535),
                                TextInput::make('default_duration')
                                    ->label('Default duration (minutes)')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->gte('min_duration')
                                    ->lte('max_duration'),
                                TextInput::make('max_duration')
                                    ->label('Maximum duration (minutes)')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->maxValue(65535)
                                    ->gte('min_duration'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
