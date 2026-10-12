<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StudentDeviceManager;
use Illuminate\Auth\Events\Logout;

class RevokeStudentDeviceOnLogout
{
    public function __construct(private StudentDeviceManager $deviceManager) {}

    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user instanceof User && $event->user->role === UserRole::Student) {
            $this->deviceManager->signOut($event->user, request());
        }
    }
}
