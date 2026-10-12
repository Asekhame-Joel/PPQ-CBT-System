<?php

namespace App\Support;

use App\Models\DeviceSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentDeviceManager
{
    public const CookieName = 'examforge_device';

    public const MaximumActiveDevices = 2;

    public function registerOrTouch(User $student, Request $request): DeviceSession
    {
        [$deviceKey, $isNewDeviceKey] = $this->deviceKey($request);
        $deviceKeyHash = hash('sha256', $deviceKey);
        $sessionIdHash = hash('sha256', $request->session()->getId());

        $existingDevice = DeviceSession::query()
            ->whereBelongsTo($student)
            ->where('device_key_hash', $deviceKeyHash)
            ->first();

        if ($existingDevice?->disabled_at !== null) {
            throw ValidationException::withMessages([
                'data.email' => 'This device has been disabled for this account. Please contact support.',
            ]);
        }

        if ($existingDevice?->isActive()) {
            if ($existingDevice->last_seen_at->lt(now()->subMinute())
                || $existingDevice->session_id_hash !== $sessionIdHash
                || $existingDevice->ip_address !== $request->ip()) {
                $existingDevice->update([
                    'session_id_hash' => $sessionIdHash,
                    'ip_address' => $request->ip(),
                    'last_seen_at' => now(),
                ]);
            }

            return $existingDevice;
        }

        $device = DB::transaction(function () use ($student, $request, $deviceKeyHash, $sessionIdHash): DeviceSession {
            User::query()->whereKey($student)->lockForUpdate()->firstOrFail();

            $activeDevices = DeviceSession::query()
                ->whereBelongsTo($student)
                ->whereNull('disabled_at')
                ->whereNull('signed_out_at')
                ->where('last_seen_at', '>=', now()->subMinutes((int) config('session.lifetime', 120)))
                ->where('device_key_hash', '!=', $deviceKeyHash)
                ->count();

            if ($activeDevices >= self::MaximumActiveDevices) {
                throw ValidationException::withMessages([
                    'data.email' => 'This account is already signed in on two devices. Sign out another device or contact support.',
                ]);
            }

            return DeviceSession::query()->updateOrCreate(
                [
                    'user_id' => $student->getKey(),
                    'device_key_hash' => $deviceKeyHash,
                ],
                [
                    'session_id_hash' => $sessionIdHash,
                    'device_name' => $this->deviceName($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'signed_in_at' => now(),
                    'last_seen_at' => now(),
                    'signed_out_at' => null,
                ],
            );
        });

        if ($isNewDeviceKey) {
            Cookie::queue(
                self::CookieName,
                $deviceKey,
                60 * 24 * 365,
                '/',
                null,
                (bool) (config('session.secure') ?? $request->isSecure()),
                true,
                false,
                'lax',
            );
        }

        return $device;
    }

    public function signOut(User $student, Request $request): void
    {
        $deviceKey = $request->cookie(self::CookieName);

        if (! is_string($deviceKey) || ! $this->isValidDeviceKey($deviceKey)) {
            return;
        }

        DeviceSession::query()
            ->whereBelongsTo($student)
            ->where('device_key_hash', hash('sha256', $deviceKey))
            ->whereNull('signed_out_at')
            ->update(['signed_out_at' => now()]);
    }

    public function disable(DeviceSession $device, User $administrator): void
    {
        $device->update([
            'disabled_at' => now(),
            'disabled_by' => $administrator->getKey(),
            'signed_out_at' => now(),
        ]);
    }

    public function enable(DeviceSession $device): void
    {
        $device->update([
            'disabled_at' => null,
            'disabled_by' => null,
        ]);
    }

    /** @return array{string, bool} */
    private function deviceKey(Request $request): array
    {
        $deviceKey = $request->cookie(self::CookieName);

        if (is_string($deviceKey) && $this->isValidDeviceKey($deviceKey)) {
            return [$deviceKey, false];
        }

        return [Str::random(64), true];
    }

    private function isValidDeviceKey(string $deviceKey): bool
    {
        return preg_match('/^[A-Za-z0-9]{64}$/', $deviceKey) === 1;
    }

    private function deviceName(?string $userAgent): string
    {
        $userAgent ??= '';

        $browser = match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/'), str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Browser',
        };

        $platform = match (true) {
            str_contains($userAgent, 'iPhone') => 'iPhone',
            str_contains($userAgent, 'iPad') => 'iPad',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Macintosh'), str_contains($userAgent, 'Mac OS') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown device',
        };

        return "{$browser} on {$platform}";
    }
}
