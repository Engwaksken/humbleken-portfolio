@props(['heading', 'intro' => null])
<section aria-labelledby="auth-title" class="section px-4">
    <div class="mx-auto w-full max-w-md">
        <div class="card p-5 shadow-[0_24px_60px_-24px_rgb(20_38_61/0.3)] sm:p-8">
            <i class="fa-solid fa-lock text-2xl text-brand" aria-hidden="true"></i>
            <h1 id="auth-title" class="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading }}</h1>
            @if ($intro)
                <p class="mt-2 leading-7 text-muted">{{ $intro }}</p>
            @endif
            @if (session('status') && session('status') !== 'verification-link-sent')
                <p role="status" class="mt-6 rounded-xl bg-brand-tint px-4 py-3 text-sm font-medium text-brand-dark">{{ session('status') }}</p>
            @endif
            <div class="mt-6">
                {{ $slot }}
            </div>
        </div>
        @isset($footer)
            <div class="mt-6 text-center text-sm text-muted">{{ $footer }}</div>
        @endisset
    </div>
</section>
