<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Support\Settings;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'featuredProjects' => Project::query()->published()->ordered()->limit(3)->get(),
            'featuredServices' => Service::query()->where('is_active', true)->orderBy('sort_order')->limit(3)->get(),
            'homeTitle' => Settings::get('home_title', 'Engineering solutions for reliable, effective IT.'),
            'homeIntro' => Settings::get('home_intro', 'Technical consulting to diagnose recurring issues, improve systems, and guide technology decisions. Get clear recommendations and practical engineering support, explained without unnecessary jargon.'),
        ]);
    }
}
