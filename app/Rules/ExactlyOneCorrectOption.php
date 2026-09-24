<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ExactlyOneCorrectOption implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The answer options must be a valid list.');

            return;
        }

        $correctOptionCount = collect($value)
            ->filter(fn (mixed $option): bool => is_array($option) && ($option['is_correct'] ?? false) === true)
            ->count();

        if ($correctOptionCount !== 1) {
            $fail('Exactly one answer option must be marked as correct.');
        }
    }
}
