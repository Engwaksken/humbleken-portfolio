<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasLabel
{
    /** Received; not yet reviewed. */
    case New = 'new';

    /** We have been in touch to arrange a time. */
    case Contacted = 'contacted';

    case Confirmed = 'confirmed';

    /** The appointment took place. */
    case Completed = 'completed';

    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Declined => 'Declined',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Contacted => 'info',
            self::Confirmed => 'success',
            self::Completed => 'gray',
            self::Declined => 'danger',
        };
    }

    /** Plain-language status for the customer. */
    public function customerLabel(): string
    {
        return match ($this) {
            self::New => 'Received — awaiting review',
            self::Contacted => 'We are arranging a time',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Declined => 'Not available',
        };
    }
}
