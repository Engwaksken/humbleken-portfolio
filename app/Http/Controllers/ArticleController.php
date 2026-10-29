<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim($filters['q'] ?? '');
        $category = ArticleCategory::query()->where('slug', $filters['category'] ?? null)->first();
        $tag = Tag::query()->where('slug', $filters['tag'] ?? null)->first();

        $articles = Article::query()
            ->published()
            ->with(['category', 'tags'])
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->when($category, fn (Builder $query) => $query->where('article_category_id', $category->id))
            ->when($tag, fn (Builder $query) => $query->whereHas('tags', fn (Builder $query) => $query->whereKey($tag->id)))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        // Only offer categories that have something public in them.
        $categories = ArticleCategory::query()
            ->whereHas('articles', fn (Builder $query) => $query->published())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
            'search' => $search,
            'activeCategory' => $category,
            'activeTag' => $tag,
        ]);
    }

    public function show(string $slug): View
    {
        // Drafts and scheduled articles are indistinguishable from missing ones.
        $article = Article::query()
            ->published()
            ->with(['category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('articles.show', ['article' => $article]);
    }
}
