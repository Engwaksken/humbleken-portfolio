<?php

namespace Tests\Unit;

use App\Support\Theme;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    public function test_contrast_matches_known_wcag_values(): void
    {
        $this->assertEqualsWithDelta(21.0, Theme::contrast('#000000', '#ffffff'), 0.01);
        $this->assertEqualsWithDelta(1.0, Theme::contrast('#777777', '#777777'), 0.01);
        $this->assertEqualsWithDelta(5.28, Theme::contrast('#077784', '#ffffff'), 0.01);
    }

    public function test_default_colour_is_acceptable(): void
    {
        $this->assertNull(Theme::problem(Theme::DEFAULT_COLOR));
    }

    public function test_light_colours_are_rejected_with_a_reason(): void
    {
        $problem = Theme::problem('#ffd400');

        $this->assertNotNull($problem);
        $this->assertStringContainsString('too light', $problem);
        $this->assertStringContainsString('4.5:1', $problem);
    }

    public function test_dark_colours_are_accepted(): void
    {
        $this->assertNull(Theme::problem('#1d4ed8'));
        $this->assertNull(Theme::problem('#7a1f1f'));
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->assertNotNull(Theme::problem('red'));
        $this->assertNotNull(Theme::problem('#12345'));
        $this->assertNotNull(Theme::problem('#12345g'));
    }

    public function test_mix_blends_towards_the_second_colour(): void
    {
        $this->assertSame('#000000', Theme::mix('#000000', '#ffffff', 0));
        $this->assertSame('#ffffff', Theme::mix('#000000', '#ffffff', 1));
        $this->assertSame('#808080', Theme::mix('#000000', '#ffffff', 0.5));
    }
}
