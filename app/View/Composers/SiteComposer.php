<?php

namespace App\View\Composers;

use App\Models\Article;
use App\Models\Project;
use App\Support\Settings;
use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $brandName = Settings::get('brand_name') ?: 'IT Problem Solver';
        $brandParts = preg_split('/\s+/', trim($brandName), 2);

        $view->with([
            'brandName' => $brandName,
            'brandLead' => $brandParts[0] ?? $brandName,
            'brandRemainder' => $brandParts[1] ?? '',
            'tagline' => Settings::get('tagline') ?: 'Engineering solutions and technical consulting',
            'hasPublishedProjects' => Project::query()->published()->exists(),
            'hasPublishedArticles' => Article::query()->published()->exists(),
        ]);
    }
}
