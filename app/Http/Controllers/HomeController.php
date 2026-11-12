<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Service;
use App\Support\PageContent;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $page = PageContent::get('home');

        return view('welcome', [
            'page' => $page,
            'featuredProjects' => $page['projects']['enabled']
                ? Project::query()->published()->ordered()->limit($page['projects']['count'])->get()
                : collect(),
            'featuredServices' => $page['services']['enabled']
                ? Service::query()->where('is_active', true)->orderBy('sort_order')->limit($page['services']['count'])->get()
                : collect(),
        ]);
    }
}
