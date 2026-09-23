<?php

namespace App\Models;

use App\Enums\AttemptStatus;
use App\Enums\AttemptType;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'course_id',
    'parent_attempt_id',
    'attempt_type',
    'question_count',
    'duration_minutes',
    'started_at',
    'expires_at',
    'submitted_at',
    'status',
    'correct_count',
    'incorrect_count',
    'unanswered_count',
    'score_percentage',
])]
class QuizAttempt extends Model
{
    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parentAttempt(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_attempt_id');
    }

    public function childAttempts(): HasMany
    {
        return $this->hasMany(self::class, 'parent_attempt_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AttemptQuestion::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'attempt_type' => AttemptType::class,
            'question_count' => 'integer',
            'duration_minutes' => 'integer',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'status' => AttemptStatus::class,
            'correct_count' => 'integer',
            'incorrect_count' => 'integer',
            'unanswered_count' => 'integer',
            'score_percentage' => 'decimal:2',
        ];
    }
}
