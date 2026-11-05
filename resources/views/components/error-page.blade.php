@props([
    'code',
    'title',
    'message',
    'icon' => 'fa-triangle-exclamation',
    'showSignOut' => false,
    'showRetry' => false,
])
@php
    /*
     * Error pages must render even when the database, cache or build manifest is the
     * reason for the error, so every lookup here is guarded and falls back to defaults.
     */
    $safe = static function (callable $callback, mixed $fallback = null): mixed {
        try {
            return $callback();
        } catch (\Throwable) {
            return $fallback;
        }
    };

    $brandName = $safe(fn () => \App\Support\Settings::get('brand_name'), null) ?: config('app.name', 'Website');
    $logoUrl = $safe(fn () => \App\Support\Settings::publicFileUrl('logo'));
    $faviconUrl = $safe(fn () => \App\Support\Settings::publicFileUrl('favicon'));
    $assets = $safe(fn () => app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css']));
    $themeTags = $assets ? $safe(fn () => \App\Support\Theme::headTags()) : null;
    $user = $showSignOut ? $safe(fn () => auth()->user()) : null;
    $homeUrl = $safe(fn () => route('home'), url('/'));
    $contactUrl = $safe(fn () => route('contact'));
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} ({{ $code }}) — {{ $brandName }}</title>
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    {{ $assets }}
    {{ $themeTags }}
    {{-- Minimal fallback styling for when the compiled stylesheet is unavailable. It sits in
         the base layer so the site's utility classes still win when the stylesheet loads. --}}
    <style>
        @layer base {
            body { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f6f8fb; color: #14263d; }
            .error-fallback-wrap { min-height: 100vh; display: flex; flex-direction: column; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body class="bg-canvas font-sans text-ink antialiased">
    <div class="error-fallback-wrap flex min-h-screen flex-col">
        <header class="border-b border-line bg-white">
            <div class="container-site flex h-16 items-center justify-between gap-4 lg:h-[4.5rem]">
                <a href="{{ $homeUrl }}" class="flex items-center gap-2.5 text-lg font-bold tracking-tight text-ink" aria-label="{{ $brandName }} home">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-9 w-auto max-w-[10rem] object-contain sm:h-10">
                    @else
                        <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl bg-ink text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($brandName, 0, 1)) }}</span>
                        <span>{{ $brandName }}</span>
                    @endif
                </a>
                @if ($contactUrl)
                    <a href="{{ $contactUrl }}" class="btn-primary !px-5 !py-2.5">Contact</a>
                @endif
            </div>
        </header>

        <main id="main-content" class="flex flex-1 items-center py-16 sm:py-24">
            <div class="container-site">
                <div class="mx-auto max-w-xl text-center">
                    <i class="fa-solid {{ $icon }} text-5xl text-brand" aria-hidden="true"></i>
                    <p class="mt-6 text-sm font-bold tracking-[.18em] text-brand uppercase">Error {{ $code }}</p>
                    <h1 class="mt-3 text-[clamp(2rem,5vw,3rem)] leading-tight font-semibold tracking-tight text-balance">{{ $title }}</h1>
                    <p class="mt-4 text-lg leading-8 text-muted text-pretty">{{ $message }}</p>

                    {{ $slot }}

                    @if ($user)
                        <div class="card mt-8 p-5 text-left text-sm">
                            <p>You are signed in as <strong class="font-semibold">{{ $user->email }}</strong>. If this page needs a different account, sign out and sign in again with that account.</p>
                            <form method="POST" action="{{ $safe(fn () => route('logout'), url('/logout')) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="btn-dark w-full sm:w-auto"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Sign out</button>
                            </form>
                        </div>
                    @endif

                    <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                        @if ($showRetry)
                            <button type="button" onclick="window.location.reload()" class="btn-primary"><i class="fa-solid fa-rotate-right text-xs" aria-hidden="true"></i> Try again</button>
                            <a href="{{ $homeUrl }}" class="btn-ghost"><i class="fa-solid fa-house text-xs" aria-hidden="true"></i> Go to the home page</a>
                        @else
                            <a href="{{ $homeUrl }}" class="btn-primary"><i class="fa-solid fa-house text-xs" aria-hidden="true"></i> Go to the home page</a>
                            <button type="button" onclick="history.length > 1 ? history.back() : (window.location.href = '{{ $homeUrl }}')" class="btn-ghost"><i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Go back</button>
                        @endif
                    </div>

                    @if ($contactUrl)
                        <p class="mt-8 text-sm text-muted">Still stuck? <a href="{{ $contactUrl }}" class="font-semibold text-brand hover:text-brand-dark">Contact us</a> and tell us what you were trying to do.</p>
                    @endif
                </div>
            </div>
        </main>

        <footer class="border-t border-line bg-white">
            <p class="container-site py-6 text-center text-sm text-muted">© {{ now()->year }} {{ $brandName }}</p>
        </footer>
    </div>
</body>
</html>
