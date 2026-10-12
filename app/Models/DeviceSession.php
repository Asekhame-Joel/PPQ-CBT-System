<?php

namespace App\Models;

use Database\Factories\DeviceSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'device_key_hash',
    'session_id_hash',
    'device_name',
    'user_agent',
    'ip_address',
    'signed_in_at',
    'last_seen_at',
    'signed_out_at',
    'disabled_at',
    'disabled_by',
])]
class DeviceSession extends Model
{
    /** @use HasFactory<DeviceSessionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disabled_by');
    }

    public function isActive(): bool
    {
        return $this->disabled_at === null
            && $this->signed_out_at === null
            && $this->last_seen_at->greaterThanOrEqualTo(
                now()->subMinutes((int) config('session.lifetime', 120)),
            );
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query
            ->whereNull('disabled_at')
            ->whereNull('signed_out_at')
            ->where('last_seen_at', '>=', now()->subMinutes((int) config('session.lifetime', 120)));
    }

    protected function casts(): array
    {
        return [
            'signed_in_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'signed_out_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }
}
