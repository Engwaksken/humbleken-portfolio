<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a featured image is shown on an article or service page.
 */
enum HeroImagePosition: string implements HasLabel
{
    /** Full width, between the page header and the content. */
    case Below = 'below';

    case Right = 'right';

    case Left = 'left';

    /** Fills the page header behind the title, under a dark overlay. */
    case Background = 'background';

    public function getLabel(): string
    {
        return match ($this) {
            self::Below => 'Below the title (full width)',
            self::Right => 'Beside the title, on the right',
            self::Left => 'Beside the title, on the left',
            self::Background => 'Fill the page header, behind the title',
        };
    }

    /** Value used by the page-hero component, or null when the image is not in the header. */
    public function heroPosition(): ?string
    {
        return $this === self::Below ? null : $this->value;
    }
}
