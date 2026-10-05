<?php

namespace App\Filament\Resources\CourseQuestionRequests\Pages;

use App\Filament\Resources\CourseQuestionRequests\CourseQuestionRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCourseQuestionRequests extends ManageRecords
{
    protected static string $resource = CourseQuestionRequestResource::class;

    public function getSubheading(): string
    {
        return 'Review requests for new question banks and leave an update for each student.';
    }
}
