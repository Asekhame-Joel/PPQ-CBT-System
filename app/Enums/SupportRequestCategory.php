<?php

namespace App\Enums;

enum SupportRequestCategory: string
{
    case AccountCorrection = 'account_correction';
    case PaymentRectification = 'payment_rectification';
    case CourseAccess = 'course_access';
    case PracticeIssue = 'practice_issue';
    case GeneralHelp = 'general_help';

    public function label(): string
    {
        return match ($this) {
            self::AccountCorrection => 'Account correction',
            self::PaymentRectification => 'Payment rectification',
            self::CourseAccess => 'Course access problem',
            self::PracticeIssue => 'Practice or result issue',
            self::GeneralHelp => 'General help',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category): array => [$category->value => $category->label()])
            ->all();
    }
}
