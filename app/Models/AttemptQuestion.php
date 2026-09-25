<?php

namespace App\Models;

use Database\Factories\AttemptQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'quiz_attempt_id',
    'question_id',
    'position',
    'question_snapshot',
    'options_snapshot',
    'correct_option_snapshot',
    'explanation_snapshot',
])]
class AttemptQuestion extends Model
{
    /** @use HasFactory<AttemptQuestionFactory> */
    use HasFactory;

    public function quizAttempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answer(): HasOne
    {
        return $this->hasOne(AttemptAnswer::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(QuestionReport::class);
    }

    public function optionText(?int $optionId): ?string
    {
        if ($optionId === null) {
            return null;
        }

        $option = collect($this->options_snapshot)
            ->first(fn (array $option): bool => (int) $option['id'] === $optionId);

        return is_array($option) ? (string) $option['text'] : null;
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'options_snapshot' => 'array',
            'correct_option_snapshot' => 'integer',
        ];
    }
}
