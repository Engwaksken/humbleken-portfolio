<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\NotificationPreferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets a signed-in customer choose which emails they receive.
 */
class NotificationPreferenceController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $request->validate(['preferences' => ['nullable', 'array']]);

        $user = $request->user();
        // Unchecked boxes are not submitted, so anything missing means "off".
        $user->notification_preferences = array_replace_recursive(
            $user->notification_preferences ?? [],
            NotificationPreferences::sanitize(
                $request->input('preferences', []),
                NotificationPreferences::CUSTOMER_EVENTS,
                NotificationPreferences::CUSTOMER_CHANNELS,
            ),
        );
        $user->save();

        return redirect()->to(route('account.dashboard').'#notifications-title')->with('notifications_saved', true);
    }
}
