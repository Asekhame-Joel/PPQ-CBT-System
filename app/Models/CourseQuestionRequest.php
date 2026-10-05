<?php

namespace App\Models;

use App\Enums\CourseQuestionRequestStatus;
use Database\Factories\CourseQuestionRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'course_code',
    'course_name',
    'message',
    'status',
    'admin_notes',
    'reviewed_by',
    'reviewed_at',
])]
class CourseQuestionRequest extends Model
{
    /** @use HasFactory<CourseQuestionRequestFactory> */
    use HasFactory;

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    protected function casts(): array
    {
        return [
            'status' => CourseQuestionRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
