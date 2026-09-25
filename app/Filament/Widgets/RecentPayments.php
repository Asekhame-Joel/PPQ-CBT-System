<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentPayments extends TableWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest payments')
            ->description('The most recent payment attempts across all students.')
            ->query(fn (): Builder => Payment::query()->with(['user', 'course'])->latest()->limit(5))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Student')
                    ->description(fn (Payment $record): string => $record->user->email),
                TextColumn::make('course.code')
                    ->label('Course'),
                TextColumn::make('amount')
                    ->money(fn (Payment $record): string => $record->currency),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => ucfirst($state->value))
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Successful => 'success',
                        PaymentStatus::Pending => 'warning',
                        PaymentStatus::Failed => 'danger',
                        PaymentStatus::Cancelled => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since(),
            ])
            ->recordActions([
                Action::make('viewPayments')
                    ->label('Payments')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(PaymentResource::getUrl('index')),
            ])
            ->paginated(false);
    }
}
