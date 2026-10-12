<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\StudentDeviceManager;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TrackStudentDevice
{
    public function __construct(private StudentDeviceManager $deviceManager) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $student = Filament::auth()->user();

        if ($student instanceof User && $student->role === UserRole::Student) {
            try {
                $this->deviceManager->registerOrTouch($student, $request);
            } catch (ValidationException $exception) {
                $message = collect($exception->errors())->flatten()->first()
                    ?? 'This device cannot access the account.';

                Filament::auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect(Filament::getPanel('student')->getLoginUrl())
                    ->with('device_access_error', $message);
            }
        }

        return $next($request);
    }
}
