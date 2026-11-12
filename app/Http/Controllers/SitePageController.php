<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\PageContent;
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
            'page' => PageContent::get('services'),
        ]);
    }

    public function service(string $slug): View
    {
        $service = Service::query()->where('is_active', true)->with('category')->where('slug', $slug)->firstOrFail();

        return view('pages.service', compact('service'));
    }

    public function approach(): View
    {
        return view('pages.approach', ['page' => PageContent::get('approach')]);
    }

    public function booking(): View
    {
        return view('pages.booking', [
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'page' => PageContent::get('booking'),
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
            'page' => PageContent::get('contact'),
        ]);
    }
}
