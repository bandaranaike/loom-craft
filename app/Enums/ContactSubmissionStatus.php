<?php

namespace App\Enums;

enum ContactSubmissionStatus: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Replied = 'replied';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::Replied => 'Replied',
            self::Closed => 'Closed',
        };
    }
}
