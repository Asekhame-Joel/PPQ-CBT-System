<?php

namespace App\Filament\Student\Pages\Auth;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Level;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use SensitiveParameter;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent()
                    ->maxLength(150),
                $this->getEmailFormComponent(),
                TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ->required()
                    ->maxLength(30),
                Select::make('department_id')
                    ->label('Department')
                    ->options(fn (): array => Department::active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->rules([
                        Rule::exists('departments', 'id')->where('is_active', true),
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
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
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(#[SensitiveParameter] array $data): array
    {
        return [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'department_id' => $data['department_id'],
            'level_id' => $data['level_id'],
            'password' => $data['password'],
            'role' => UserRole::Student,
            'is_active' => true,
        ];
    }
}
