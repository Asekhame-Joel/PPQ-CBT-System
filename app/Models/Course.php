<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'level_id',
    'code',
    'name',
    'description',
    'price',
    'default_question_count',
    'default_duration',
    'min_question_count',
    'max_question_count',
    'min_duration',
    'max_duration',
    'is_active',
])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function courseAccesses(): HasMany
    {
        return $this->hasMany(CourseAccess::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function eligibleFor(Builder $query, User $student): Builder
    {
        if (! $student->level_id || ! $student->department_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('level_id', $student->level_id)
            ->where(fn (Builder $query): Builder => $query
                ->whereDoesntHave('departments')
                ->orWhereHas('departments', fn (Builder $query): Builder => $query
                    ->whereKey($student->department_id)));
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'default_question_count' => 'integer',
            'default_duration' => 'integer',
            'min_question_count' => 'integer',
            'max_question_count' => 'integer',
            'min_duration' => 'integer',
            'max_duration' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
