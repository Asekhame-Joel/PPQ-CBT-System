<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class StudentActionRateLimiter
{
    public function ensure(User $student, string $action, int $maximumAttempts, int $decaySeconds): void
    {
        $key = "student-action:{$action}:{$student->id}";

        if (RateLimiter::tooManyAttempts($key, $maximumAttempts)) {
            $seconds = max(1, RateLimiter::availableIn($key));

            throw ValidationException::withMessages([
                'action' => "Please wait {$seconds} seconds before trying again.",
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
