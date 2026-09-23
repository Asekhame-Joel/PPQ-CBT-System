<?php

namespace App\Models;

use App\Enums\AccessSource;
use Database\Factories\CourseAccessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'course_id',
    'payment_id',
    'access_source',
    'granted_at',
    'expires_at',
    'is_active',
])]
class CourseAccess extends Model
{
    /** @use HasFactory<CourseAccessFactory> */
    use HasFactory;

    protected $table = 'course_access';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    #[Scope]
    protected function available(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));
    }

    protected function casts(): array
    {
        return [
            'access_source' => AccessSource::class,
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
