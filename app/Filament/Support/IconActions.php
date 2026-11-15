<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;

/**
 * Table row actions as compact icon buttons. The action's label shows as a tooltip on hover
 * and stays available to screen readers, so nothing is lost by hiding the text.
 */
class IconActions
{
    /**
     * @param  array<Action|ActionGroup>  $actions
     * @return array<Action|ActionGroup>
     */
    public static function make(array $actions): array
    {
        return array_map(static function (Action|ActionGroup $action): Action|ActionGroup {
            if ($action instanceof Action) {
                // Resolved at render time, so labels that depend on the record still work.
                $action->iconButton()->tooltip(fn (): ?string => (string) $action->getLabel() ?: null);
            }

            return $action;
        }, $actions);
    }
}
