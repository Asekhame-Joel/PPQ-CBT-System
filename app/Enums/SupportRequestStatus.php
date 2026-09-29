<?php

namespace App\Enums;

enum SupportRequestStatus: string
{
    case Open = 'open';
    case InReview = 'in_review';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InReview => 'In review',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
