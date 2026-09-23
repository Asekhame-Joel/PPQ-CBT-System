<?php

namespace App\Models;

use Database\Factories\StudentSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'preferred_question_count',
    'preferred_duration',
    'randomize_questions',
    'randomize_options',
])]
class StudentSetting extends Model
{
    /** @use HasFactory<StudentSettingFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'preferred_question_count' => 'integer',
            'preferred_duration' => 'integer',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
        ];
    }
}
