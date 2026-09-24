<?php

namespace App\Filament\Resources\QuestionReports\Pages;

use App\Enums\QuestionReportStatus;
use App\Filament\Resources\QuestionReports\QuestionReportResource;
use Filament\Resources\Pages\EditRecord;

class EditQuestionReport extends EditRecord
{
    protected static string $resource = QuestionReportResource::class;

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $status = QuestionReportStatus::from($data['status']);

        if (in_array($status, [QuestionReportStatus::Resolved, QuestionReportStatus::Dismissed], true)) {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        } else {
            $data['resolved_by'] = null;
            $data['resolved_at'] = null;
        }

        return $data;
    }
}
