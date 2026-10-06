<?php

namespace App\Filament\Resources\QuestionImportBatches\Pages;

use App\Filament\Resources\QuestionImportBatches\QuestionImportBatchResource;
use Filament\Resources\Pages\ManageRecords;

class ManageQuestionImportBatches extends ManageRecords
{
    protected static string $resource = QuestionImportBatchResource::class;

    public function getSubheading(): string
    {
        return 'Each DOCX or TXT import is kept as one batch. Deleting a batch removes only its live question bank; saved practice attempts remain available.';
    }
}
