<?php

namespace App\Models;

use Database\Factories\QuestionImportBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'imported_by', 'source_filename', 'source_format', 'question_count'])]
class QuestionImportBatch extends Model
{
    /** @use HasFactory<QuestionImportBatchFactory> */
    use HasFactory;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
