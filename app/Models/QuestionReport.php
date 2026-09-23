<?php

namespace App\Models;

use App\Enums\QuestionReportStatus;
use Database\Factories\QuestionReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'question_id',
    'attempt_question_id',
    'report_text',
    'status',
    'admin_notes',
    'resolved_by',
    'resolved_at',
])]
class QuestionReport extends Model
{
    /** @use HasFactory<QuestionReportFactory> */
    use HasFactory;

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(AttemptQuestion::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', QuestionReportStatus::Pending);
    }

    protected function casts(): array
    {
        return [
            'status' => QuestionReportStatus::class,
            'resolved_at' => 'datetime',
        ];
    }
}
