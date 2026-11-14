<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\AccountDeletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Lets a signed-in customer permanently delete their own account.
 */
class DeleteAccountController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            return back()->withErrors(['password' => 'Administrator accounts cannot be deleted from this page.'], 'deleteAccount');
        }

        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password'],
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Tick the box to confirm you want to delete your account.',
        ]);

        Auth::guard('web')->logout();
        AccountDeletion::delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('account_deleted', true);
    }
}
