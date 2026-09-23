<?php

namespace App\Models;

use Database\Factories\AttemptAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['attempt_question_id', 'selected_option_id', 'is_correct', 'answered_at'])]
class AttemptAnswer extends Model
{
    /** @use HasFactory<AttemptAnswerFactory> */
    use HasFactory;

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestion::class);
    }

    protected function casts(): array
    {
        return [
            'selected_option_id' => 'integer',
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }
}
