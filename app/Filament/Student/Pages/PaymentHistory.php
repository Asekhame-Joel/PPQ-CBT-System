<?php

namespace App\Filament\Student\Pages;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.student.pages.payment-history';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Payment::query()
                ->where('user_id', auth()->id())
                ->with('course'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                TextColumn::make('course.code')
                    ->label('Course')
                    ->description(fn (Payment $record): string => $record->course->name)
                    ->searchable(),
                TextColumn::make('reference')
                    ->searchable()
                    ->copyable(),
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
            ])
            ->recordActions([
                Action::make('receipt')
                    ->label('Receipt')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->url(fn (Payment $record): string => PaymentReceipt::getUrl(
                        ['payment' => $record],
                        panel: 'student',
                    ))
                    ->visible(fn (Payment $record): bool => $record->status === PaymentStatus::Successful),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Your course payment history will appear here.');
    }
}
