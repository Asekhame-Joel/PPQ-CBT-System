<?php

namespace App\Enums;

enum CourseQuestionRequestStatus: string
{
    case Requested = 'requested';
    case InReview = 'in_review';
    case Added = 'added';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::InReview => 'In review',
            self::Added => 'Questions added',
            self::Unavailable => 'Not available',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
