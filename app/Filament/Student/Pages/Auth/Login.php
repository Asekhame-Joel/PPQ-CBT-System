<?php

namespace App\Filament\Student\Pages\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;

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
    }
}
