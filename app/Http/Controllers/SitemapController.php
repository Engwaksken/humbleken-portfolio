<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $projects = Project::query()->published()->get(['slug', 'updated_at']);
        $articles = Article::query()->published()->get(['slug', 'updated_at']);

        $urls = collect([['loc' => route('home'), 'lastmod' => null]])
            ->when($projects->isNotEmpty(), fn ($urls) => $urls->push(['loc' => route('projects.index'), 'lastmod' => null]))
            ->when($articles->isNotEmpty(), fn ($urls) => $urls->push(['loc' => route('articles.index'), 'lastmod' => null]))
            ->concat($projects->map(fn (Project $project) => [
                'loc' => route('projects.show', ['project' => $project->slug]),
                'lastmod' => $project->updated_at,
            ]))
            ->concat($articles->map(fn (Article $article) => [
                'loc' => route('articles.show', ['article' => $article->slug]),
                'lastmod' => $article->updated_at,
            ]));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
