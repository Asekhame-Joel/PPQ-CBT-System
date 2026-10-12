<?php

namespace App\Filament\Student\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StudentDeviceManager;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        $user = Filament::auth()->user();

        if ($user instanceof User && $user->role !== UserRole::Student) {
            Filament::auth()->logout();
            request()->session()->regenerateToken();
        }

        parent::mount();

        if ($message = session()->pull('device_access_error')) {
            Notification::make()
                ->title('Device access denied')
                ->body((string) $message)
                ->danger()
                ->send();
        }
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if (! $response) {
            return null;
        }

        $student = Filament::auth()->user();

        if (! $student instanceof User) {
            return $response;
        }

        try {
            app(StudentDeviceManager::class)->registerOrTouch($student, request());
        } catch (ValidationException $exception) {
            Filament::auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw $exception;
        }

        return $response;
    }
}
