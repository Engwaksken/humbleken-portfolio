<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Settings;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $contact = [
            'email' => Settings::get('contact_email'),
            'phone' => Settings::get('contact_phone'),
            'whatsapp' => Settings::get('whatsapp'),
            'businessHours' => Settings::get('business_hours'),
            'address' => Settings::get('address'),
        ];
        $socialLinks = $this->socialLinks();

        $serviceCategories = ServiceCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        $services = Service::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        // Services whose category is missing or inactive are listed without a heading.
        $activeCategoryIds = $serviceCategories->pluck('id')->all();
        [$categorised, $ungroupedServices] = $services->partition(
            fn (Service $service): bool => in_array($service->service_category_id, $activeCategoryIds, true)
        );

        return view('welcome', [
            'featuredProjects' => Project::query()->published()->ordered()->limit(3)->get(),
            'contactEmail' => $contact['email'],
            'contactPhone' => $contact['phone'],
            'whatsapp' => $contact['whatsapp'],
            'businessHours' => $contact['businessHours'],
            'address' => $contact['address'],
            'socialLinks' => $socialLinks,
            'hasContact' => collect($contact)->filter()->isNotEmpty() || $socialLinks !== [],
            'services' => $services,
            'serviceCategories' => $serviceCategories,
            'servicesByCategory' => $categorised->groupBy('service_category_id'),
            'ungroupedServices' => $ungroupedServices,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function socialLinks(): array
    {
        $raw = Settings::get('social_links');
        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($decoded)) {
            return [];
        }

        $links = [];
        foreach ($decoded as $platform => $url) {
            if (is_string($url) && trim($url) !== '') {
                $links[(string) $platform] = trim($url);
            }
        }

        return $links;
    }
}
