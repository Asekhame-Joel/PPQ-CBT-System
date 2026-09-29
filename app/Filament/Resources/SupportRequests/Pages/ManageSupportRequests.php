<?php

namespace App\Filament\Resources\SupportRequests\Pages;

use App\Filament\Resources\SupportRequests\SupportRequestResource;
use Filament\Resources\Pages\ManageRecords;

class ManageSupportRequests extends ManageRecords
{
    protected static string $resource = SupportRequestResource::class;

    public function getSubheading(): string
    {
        return 'Review student requests and record the resolution or response.';
    }
}
