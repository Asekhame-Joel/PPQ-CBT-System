<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

class AuthenticateStudentPanel extends Authenticate
{
    /** @param array<string> $guards */
    protected function authenticate($request, array $guards): void
    {
        $user = Filament::auth()->user();

        if (
            $request->is('student') &&
            $user instanceof User &&
            $user->role !== UserRole::Student
        ) {
            Filament::auth()->logout();
            $request->session()->regenerateToken();
            $this->unauthenticated($request, $guards);
        }

        parent::authenticate($request, $guards);
    }
}
