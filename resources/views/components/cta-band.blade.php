@props(['title' => 'Have a technical challenge in mind?', 'text' => 'Tell us about the issue or objective and we will help you find a practical way forward.'])
<section {{ $attributes->class('pb-16 sm:pb-24') }} aria-labelledby="cta-title">
    <div class="container-site">
        <div class="rounded-3xl bg-brand-dark px-6 py-12 text-white sm:px-12 sm:py-16">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <h2 id="cta-title" class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $title }}</h2>
                    <p class="mt-3 leading-7 text-white/80">{{ $text }}</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('booking') }}" class="btn-light">Book a consultation</a>
                    <a href="{{ route('contact') }}" class="btn border border-white/40 text-white hover:bg-white/10">Contact us</a>
                </div>
            </div>
        </div>
    </div>
</section>
