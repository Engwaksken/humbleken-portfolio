<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PublicMaintenanceMode;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ServiceResource;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Tag;
use App\Support\AiChat;
use App\Support\Currency;
use App\Support\PageContent;
use App\Support\PaymentSettings;
use App\Support\PrivacyPolicy;
use App\Support\Settings;
use App\Support\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only public content for the mobile app. Only published or active records are exposed.
 */
class ContentController extends Controller
{
    /**
     * The privacy policy as Markdown, for the app's Privacy policy screen.
     */
    public function privacyPolicy(): JsonResponse
    {
        return response()->json(['data' => [
            'content' => PrivacyPolicy::markdown(),
            'updated_at' => PrivacyPolicy::updatedAt(),
            'url' => route('privacy'),
        ]]);
    }

    public function site(): JsonResponse
    {
        $links = Settings::get('social_links');
        $links = is_string($links) ? json_decode($links, true) : $links;
        $maintenance = PublicMaintenanceMode::isEnabled();

        return response()->json(['data' => [
            'brand_name' => Settings::get('brand_name') ?: 'IT Problem Solver',
            'tagline' => Settings::get('tagline') ?: 'Engineering solutions and technical consulting',
            'logo_url' => Settings::publicFileUrl('logo'),
            'sticky_logo_url' => Settings::publicFileUrl('sticky_logo'),
            'favicon_url' => Settings::publicFileUrl('favicon'),
            'theme' => ['primary' => Theme::color(), 'secondary' => Theme::secondaryColor()],
            'contact' => [
                'email' => Settings::get('contact_email') ?: null,
                'phone' => Settings::get('contact_phone') ?: null,
                'whatsapp' => Settings::get('whatsapp') ?: null,
                'business_hours' => Settings::get('business_hours') ?: null,
                'address' => Settings::get('address') ?: null,
            ],
            'social_links' => collect(is_array($links) ? $links : [])
                ->filter(fn (mixed $url): bool => is_string($url) && preg_match('#^https?://#i', trim($url)) === 1)
                ->map(fn (string $url, string|int $platform): array => ['platform' => (string) $platform, 'url' => trim($url)])
                ->values(),
            'currency' => ['code' => Currency::code(), 'symbol' => Currency::symbol(), 'decimals' => Currency::decimals()],
            'payments' => PaymentSettings::publicDetails(),
            'features' => [
                'projects' => Project::query()->published()->exists(),
                'articles' => Article::query()->published()->exists(),
                'chatbot' => AiChat::enabled(),
            ],
            'maintenance' => [
                'enabled' => $maintenance,
                'message' => $maintenance
                    ? (Settings::get('maintenance_message') ?: 'I am making some improvements and the site will be back shortly.')
                    : null,
            ],
        ]]);
    }

    public function page(Request $request, string $key): JsonResponse
    {
        abort_unless(in_array($key, PageContent::PAGES, true), 404);

        $page = PageContent::get($key);

        if ($key === 'home') {
            $page['services']['items'] = $page['services']['enabled']
                ? ServiceResource::collection(Service::query()->where('is_active', true)->with('category')->orderBy('sort_order')->limit($page['services']['count'])->get())->resolve($request)
                : [];
            $page['projects']['items'] = $page['projects']['enabled']
                ? ProjectResource::collection(Project::query()->published()->ordered()->limit($page['projects']['count'])->get())->resolve($request)
                : [];
            unset($page['services']['count'], $page['projects']['count']);
        }

        return response()->json(['data' => $page]);
    }

    public function services(Request $request): JsonResponse
    {
        $services = Service::query()->where('is_active', true)->with('category')->orderBy('sort_order')->get();

        return response()->json([
            'data' => ServiceResource::collection($services)->resolve($request),
            'categories' => ServiceCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ServiceCategory $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                ]),
        ]);
    }

    public function service(string $slug): ServiceResource
    {
        $service = Service::query()->where('is_active', true)->with('category')->where('slug', $slug)->firstOrFail();

        return (new ServiceResource($service))->detailed();
    }

    public function projects(): AnonymousResourceCollection
    {
        return ProjectResource::collection(Project::query()->published()->ordered()->paginate(12));
    }

    public function project(string $slug): ProjectResource
    {
        return (new ProjectResource(Project::query()->published()->where('slug', $slug)->firstOrFail()))->detailed();
    }

    public function articles(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim($filters['q'] ?? '');
        $category = filled($filters['category'] ?? null) ? ArticleCategory::query()->where('slug', $filters['category'])->first() : null;
        $tag = filled($filters['tag'] ?? null) ? Tag::query()->where('slug', $filters['tag'])->first() : null;

        $articles = Article::query()
            ->published()
            ->with(['category', 'tags'])
            ->when($search !== '', fn (Builder $query) => $query->search($search))
            ->when($category, fn (Builder $query) => $query->where('article_category_id', $category->id))
            ->when($tag, fn (Builder $query) => $query->whereHas('tags', fn (Builder $query) => $query->whereKey($tag->id)))
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return ArticleResource::collection($articles);
    }

    public function article(string $slug): ArticleResource
    {
        $article = Article::query()->published()->with(['category', 'tags'])->where('slug', $slug)->firstOrFail();

        return (new ArticleResource($article))->detailed();
    }

    public function articleCategories(): JsonResponse
    {
        return response()->json([
            'data' => ArticleCategory::query()
                ->whereHas('articles', fn (Builder $query) => $query->published())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (ArticleCategory $category): array => ['name' => $category->name, 'slug' => $category->slug]),
        ]);
    }
}
