<?php

namespace App\Filament\Resources\SupportRequests;

use App\Enums\SupportRequestCategory;
use App\Enums\SupportRequestStatus;
use App\Filament\Resources\SupportRequests\Pages\ManageSupportRequests;
use App\Models\SupportRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportRequestResource extends Resource
{
    protected static ?string $model = SupportRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Review';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Support requests';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.email')
                    ->label('Student')
                    ->description(fn (SupportRequest $record): string => $record->student->name)
                    ->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (SupportRequestCategory $state): string => $state->label()),
                TextColumn::make('subject')
                    ->limit(48)
                    ->searchable(),
                TextColumn::make('course.code')
                    ->label('Course')
                    ->placeholder('—'),
                TextColumn::make('payment.reference')
                    ->label('Payment reference')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (SupportRequestStatus $state): string => $state->label())
                    ->color(fn (SupportRequestStatus $state): string => match ($state) {
                        SupportRequestStatus::Open => 'warning',
                        SupportRequestStatus::InReview => 'info',
                        SupportRequestStatus::Resolved => 'success',
                        SupportRequestStatus::Closed => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(SupportRequestCategory::options()),
                SelectFilter::make('status')->options([
                    SupportRequestStatus::Open->value => 'Open',
                    SupportRequestStatus::InReview->value => 'In review',
                    SupportRequestStatus::Resolved->value => 'Resolved',
                    SupportRequestStatus::Closed->value => 'Closed',
                ]),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->modalHeading('Review support request')
                    ->modalDescription(fn (SupportRequest $record): string => $record->message)
                    ->schema([
                        Select::make('status')
                            ->options([
                                SupportRequestStatus::Open->value => 'Open',
                                SupportRequestStatus::InReview->value => 'In review',
                                SupportRequestStatus::Resolved->value => 'Resolved',
                                SupportRequestStatus::Closed->value => 'Closed',
                            ])
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('Reply / admin notes')
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ])
                    ->fillForm(fn (SupportRequest $record): array => [
                        'status' => $record->status->value,
                        'admin_notes' => $record->admin_notes,
                    ])
                    ->action(function (SupportRequest $record, array $data): void {
                        $status = SupportRequestStatus::from($data['status']);

                        $record->update([
                            'status' => $status,
                            'admin_notes' => $data['admin_notes'],
                            'resolved_by' => $status === SupportRequestStatus::Resolved ? auth()->id() : null,
                            'resolved_at' => $status === SupportRequestStatus::Resolved ? now() : null,
                        ]);

                        Notification::make()
                            ->title('Support request updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No support requests yet')
            ->emptyStateDescription('Student account, payment, course access, and general support requests will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSupportRequests::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
