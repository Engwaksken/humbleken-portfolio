@props(['heading', 'intro' => null])
<section aria-labelledby="auth-title" class="page-hero min-h-[70vh] border-b-0 px-4 py-14 sm:py-20">
    <div class="mx-auto w-full max-w-md">
        <div class="card p-6 shadow-[0_24px_60px_-24px_rgb(20_38_61/0.3)] sm:p-9">
            <span class="grid size-12 place-items-center rounded-2xl bg-brand-tint text-brand-dark" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-6 fill-none stroke-current" stroke-width="1.9"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke-linecap="round"/></svg>
            </span>
            <h1 id="auth-title" class="mt-5 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading }}</h1>
            @if ($intro)
                <p class="mt-2 leading-7 text-muted">{{ $intro }}</p>
            @endif
            @if (session('status') && session('status') !== 'verification-link-sent')
                <p role="status" class="mt-6 rounded-xl bg-brand-tint px-4 py-3 text-sm font-medium text-brand-dark">{{ session('status') }}</p>
            @endif
            <div class="mt-7">
                {{ $slot }}
            </div>
        </div>
        @isset($footer)
            <div class="mt-6 text-center text-sm text-muted">{{ $footer }}</div>
        @endisset
    </div>
</section>
