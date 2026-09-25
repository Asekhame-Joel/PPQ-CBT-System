<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Payments\ApprovePendingPayment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.email')
                    ->label('Student')
                    ->description(fn (Payment $record): string => $record->user->name)
                    ->searchable(),
                TextColumn::make('course.code')
                    ->label('Course')
                    ->description(fn (Payment $record): string => $record->course->name)
                    ->searchable(),
                TextColumn::make('amount')
                    ->money(fn (Payment $record): string => $record->currency)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => ucfirst($state->value))
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::Successful => 'success',
                        PaymentStatus::Pending => 'warning',
                        PaymentStatus::Failed => 'danger',
                        PaymentStatus::Cancelled => 'gray',
                    }),
                TextColumn::make('provider')
                    ->formatStateUsing(fn (PaymentProvider $state): string => ucfirst($state->value))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->placeholder('Not paid')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        PaymentStatus::Pending->value => 'Pending',
                        PaymentStatus::Successful->value => 'Successful',
                        PaymentStatus::Failed->value => 'Failed',
                        PaymentStatus::Cancelled->value => 'Cancelled',
                    ]),
                SelectFilter::make('provider')
                    ->options([
                        PaymentProvider::Paystack->value => 'Paystack',
                    ]),
                SelectFilter::make('course')
                    ->relationship('course', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve & unlock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve pending payment?')
                    ->modalDescription('This confirms the payment manually and immediately unlocks the course for the student.')
                    ->visible(fn (Payment $record): bool => $record->status === PaymentStatus::Pending)
                    ->action(function (Payment $record, ApprovePendingPayment $approvePayment): void {
                        /** @var User $admin */
                        $admin = auth()->user();
                        $approvePayment->handle($record, $admin);

                        Notification::make()
                            ->title('Payment approved and course unlocked')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No payments found')
            ->emptyStateDescription('Pending and completed student payments will appear here.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->defaultSort('created_at', 'desc');
    }
}
