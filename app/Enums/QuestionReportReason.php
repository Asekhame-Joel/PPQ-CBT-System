<?php

namespace App\Enums;

enum QuestionReportReason: string
{
    case IncorrectAnswer = 'incorrect_answer';
    case UnclearQuestion = 'unclear_question';
    case WrongExplanation = 'wrong_explanation';
    case DuplicateQuestion = 'duplicate_question';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IncorrectAnswer => 'Incorrect answer',
            self::UnclearQuestion => 'Unclear question',
            self::WrongExplanation => 'Wrong explanation',
            self::DuplicateQuestion => 'Duplicate question',
            self::Other => 'Other',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $reason): array => [$reason->value => $reason->label()])
            ->all();
    }
}
