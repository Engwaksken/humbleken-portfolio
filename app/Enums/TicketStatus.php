<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasLabel
{
    /** Waiting for a response from us. */
    case Open = 'open';

    /** We replied; waiting for the customer. */
    case Answered = 'answered';

    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Answered => 'Answered',
            self::Closed => 'Closed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'warning',
            self::Answered => 'info',
            self::Closed => 'gray',
        };
    }
}
