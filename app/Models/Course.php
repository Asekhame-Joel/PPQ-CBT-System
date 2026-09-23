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

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
