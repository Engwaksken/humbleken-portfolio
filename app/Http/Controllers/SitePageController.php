<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Settings;
use Illuminate\Contracts\View\View;

class SitePageController extends Controller
{
    public function services(): View
    {
        $categories = ServiceCategory::query()->where('is_active', true)->orderBy('sort_order')->get();
        $services = Service::query()->where('is_active', true)->with('category')->orderBy('sort_order')->get();

        return view('pages.services', [
            'categories' => $categories,
            'services' => $services,
            'servicesByCategory' => $services->groupBy('service_category_id'),
            'pageTitle' => Settings::get('services_title', 'Technical expertise for systems and decisions.'),
            'pageIntro' => Settings::get('services_intro', 'Focused consulting and engineering support to address operational issues, strengthen systems, and make informed technology choices.'),
        ]);
    }

    public function service(string $slug): View
    {
        $service = Service::query()->where('is_active', true)->with('category')->where('slug', $slug)->firstOrFail();

        return view('pages.service', compact('service'));
    }

    public function approach(): View
    {
        return view('pages.approach', [
            'pageTitle' => Settings::get('approach_title', 'Clear analysis. Practical recommendations.'),
            'pageIntro' => Settings::get('approach_intro', 'A straightforward process to understand the challenge, evaluate options, and move forward with confidence.'),
            'approachSteps' => Settings::get('approach_steps', "Understand the current environment, the issue, and the outcome you need.\nAssess options and explain technical implications and trade-offs in clear language.\nDevelop a proportionate path forward focused on effective engineering and reliable systems."),
        ]);
    }

    public function booking(): View
    {
        return view('pages.booking', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'pageTitle' => Settings::get('booking_title', 'Request a consultation.'),
            'pageIntro' => Settings::get('booking_intro', 'Share a preferred time and we’ll get back to you to confirm availability. This is a request, not a confirmed booking.'),
        ]);
    }

    public function contact(): View
    {
        $links = Settings::get('social_links');
        $links = is_string($links) ? json_decode($links, true) : $links;
        $socialLinks = collect(is_array($links) ? $links : [])->filter(fn ($url) => is_string($url) && preg_match('#^https?://#i', trim($url)))->all();

        return view('pages.contact', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'contactEmail' => Settings::get('contact_email'),
            'contactPhone' => Settings::get('contact_phone'),
            'whatsapp' => Settings::get('whatsapp'),
            'businessHours' => Settings::get('business_hours'),
            'address' => Settings::get('address'),
            'socialLinks' => $socialLinks,
            'pageTitle' => Settings::get('contact_title', 'Discuss your technical requirements.'),
            'pageIntro' => Settings::get('contact_intro', 'Share the issue, objective, or decision you are working through to begin a focused discussion.'),
        ]);
    }
}
