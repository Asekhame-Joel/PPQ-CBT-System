<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $levels = $this->form->getRawState()['levels'] ?? [];
        $data['level_id'] = $levels[0] ?? $this->record->level_id;

        return $data;
    }
}
