<?php

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Core operations';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->where('status', PaymentStatus::Pending)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment details')
                    ->schema([
                        Select::make('user_id')
                            ->label('Student')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('course_id')
                            ->label('Course')
                            ->relationship('course', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('reference')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150),
                        TextInput::make('amount')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('currency')
                            ->default('NGN')
                            ->maxLength(10)
                            ->required(),
                        Select::make('status')
                            ->options([
                                PaymentStatus::Pending->value => 'Pending',
                                PaymentStatus::Successful->value => 'Successful',
                                PaymentStatus::Failed->value => 'Failed',
                                PaymentStatus::Cancelled->value => 'Cancelled',
                            ])
                            ->default(PaymentStatus::Pending->value)
                            ->required(),
                        Select::make('provider')
                            ->options(['paystack' => 'Paystack'])
                            ->default('paystack')
                            ->required(),
                        DateTimePicker::make('paid_at')
                            ->label('Payment date'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}
