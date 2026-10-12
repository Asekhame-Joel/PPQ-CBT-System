<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Models\DeviceSession;
use App\Models\User;
use App\Support\StudentDeviceManager;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeviceSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'deviceSessions';

    protected static ?string $title = 'Account devices';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_name')
            ->columns([
                TextColumn::make('device_name')
                    ->label('Device')
                    ->description(fn (DeviceSession $record): string => $record->ip_address ?? 'IP unavailable')
                    ->searchable(),
                TextColumn::make('status')
                    ->state(fn (DeviceSession $record): string => match (true) {
                        $record->disabled_at !== null => 'Disabled',
                        $record->isActive() => 'Signed in',
                        default => 'Signed out',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Signed in' => 'success',
                        'Disabled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('signed_in_at')
                    ->label('Signed in')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_seen_at')
                    ->label('Last active')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('disabledBy.name')
                    ->label('Disabled by')
                    ->placeholder('—'),
            ])
            ->recordActions([
                Action::make('disableDevice')
                    ->label('Disable device')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('This device will be signed out and prevented from signing into this account again until you enable it.')
                    ->visible(fn (DeviceSession $record): bool => $record->disabled_at === null)
                    ->action(function (DeviceSession $record, StudentDeviceManager $deviceManager): void {
                        $administrator = Filament::auth()->user();

                        abort_unless($administrator instanceof User, 403);
                        $deviceManager->disable($record, $administrator);

                        Notification::make()
                            ->title('Device disabled')
                            ->success()
                            ->send();
                    }),
                Action::make('enableDevice')
                    ->label('Enable device')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DeviceSession $record): bool => $record->disabled_at !== null)
                    ->action(function (DeviceSession $record, StudentDeviceManager $deviceManager): void {
                        $deviceManager->enable($record);

                        Notification::make()
                            ->title('Device enabled')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('last_seen_at', 'desc')
            ->emptyStateHeading('No devices recorded')
            ->emptyStateDescription('Devices will appear after this student signs in.');
    }
}
