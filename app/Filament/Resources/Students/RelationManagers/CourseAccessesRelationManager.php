<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\CourseAccess\GrantCourseAccess;
use App\CourseAccess\RevokeManualCourseAccess;
use App\Enums\AccessSource;
use App\Models\Course;
use App\Models\CourseAccess;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseAccessesRelationManager extends RelationManager
{
    protected static string $relationship = 'courseAccesses';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('course.code')
                    ->label('Course')
                    ->description(fn (CourseAccess $record): string => $record->course->name)
                    ->searchable(),
                TextColumn::make('access_source')
                    ->label('Source')
                    ->formatStateUsing(fn (AccessSource $state): string => ucfirst($state->value))
                    ->badge()
                    ->color(fn (AccessSource $state): string => match ($state) {
                        AccessSource::Payment => 'success',
                        AccessSource::Admin => 'info',
                        AccessSource::Promotion => 'warning',
                    }),
                TextColumn::make('payment.reference')
                    ->label('Payment')
                    ->placeholder('Manual grant')
                    ->copyable(),
                TextColumn::make('granted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->placeholder('No expiry')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->headerActions([
                Action::make('grantCourse')
                    ->label('Grant or renew access')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Select::make('course_id')
                            ->label('Course')
                            ->options(fn (): array => $this->availableCourseOptions())
                            ->searchable()
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Access expires')
                            ->minDate(now())
                            ->helperText('Leave blank for access without an expiry date.'),
                    ])
                    ->action(function (array $data, GrantCourseAccess $grantAccess): void {
                        /** @var User $student */
                        $student = $this->getOwnerRecord();
                        $course = Course::query()->findOrFail($data['course_id']);

                        $grantAccess->handle(
                            $student,
                            $course,
                            filled($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
                        );

                        Notification::make()
                            ->title('Course access granted')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->visible(fn (CourseAccess $record): bool => $record->access_source === AccessSource::Admin
                        && $record->is_active)
                    ->action(function (CourseAccess $record, RevokeManualCourseAccess $revokeAccess): void {
                        $revokeAccess->handle($record);

                        Notification::make()
                            ->title('Course access revoked')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('granted_at', 'desc');
    }

    /** @return array<int, string> */
    private function availableCourseOptions(): array
    {
        /** @var User $student */
        $student = $this->getOwnerRecord();

        return Course::query()
            ->active()
            ->eligibleFor($student)
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (Course $course): array => [
                $course->getKey() => "{$course->code} — {$course->name}",
            ])
            ->all();
    }
}
