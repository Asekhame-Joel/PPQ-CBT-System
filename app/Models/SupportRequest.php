<?php

namespace App\Models;

use App\Enums\SupportRequestCategory;
use App\Enums\SupportRequestStatus;
use Database\Factories\SupportRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'course_id',
    'payment_id',
    'category',
    'subject',
    'message',
    'status',
    'admin_notes',
    'resolved_by',
    'resolved_at',
])]
class SupportRequest extends Model
{
    /** @use HasFactory<SupportRequestFactory> */
    use HasFactory;

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected function casts(): array
    {
        return [
            'category' => SupportRequestCategory::class,
            'status' => SupportRequestStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
