<?php

namespace App\Http\Controllers;

use App\Support\PrivacyPolicy;
use App\Support\Settings;
use Illuminate\Contracts\View\View;

/**
 * Public pages that app stores require: the privacy policy and account deletion help.
 */
class LegalController extends Controller
{
    public function privacy(): View
    {
        return view('pages.privacy', [
            'policy' => PrivacyPolicy::html(),
            'updatedAt' => PrivacyPolicy::updatedAt(),
        ]);
    }

    public function deleteAccount(): View
    {
        return view('pages.delete-account', [
            'contactEmail' => Settings::get('contact_email'),
        ]);
    }
}
