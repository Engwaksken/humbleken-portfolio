<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f3ed">
    <title>IT Problem Solver — Practical help with IT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f3ed] text-[#20221f] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-slate-900 focus:shadow-lg">Skip to content</a>

    <header class="border-b border-[#20221f]/15">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-5 lg:px-8">
            <a href="#home" class="text-base font-bold tracking-tight" aria-label="IT Problem Solver home">
                IT <span class="text-emerald-800">Problem Solver</span>
            </a>
            <nav aria-label="Main navigation" class="flex items-center gap-5 text-sm font-medium sm:gap-8">
                <a class="transition hover:text-emerald-800 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4" href="#services">What I help with</a>
                <a class="transition hover:text-emerald-800 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4" href="#approach">How I work</a>
                <a class="underline decoration-emerald-700 decoration-2 underline-offset-4 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4" href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        <section id="home" aria-labelledby="hero-title" class="border-b border-[#20221f]/15">
            <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 sm:py-28 lg:grid-cols-[1.4fr_.6fr] lg:items-end lg:px-8 lg:py-32">
                <div>
                    <p class="mb-7 text-xs font-bold uppercase tracking-[.2em] text-emerald-800">Independent IT consulting</p>
                    <h1 id="hero-title" class="max-w-4xl text-5xl font-semibold leading-[1.02] tracking-[-.055em] sm:text-6xl lg:text-7xl">
                        IT should make your day easier. <span class="text-emerald-800">If it doesn’t, let’s sort it out.</span>
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-[#555750]">Something broken, confusing, or taking more time than it should? I help make sense of the problem and work out a practical next step—without burying you in jargon.</p>
                    <a href="#services" class="mt-9 inline-flex items-center gap-3 border-b-2 border-emerald-700 pb-2 text-sm font-semibold hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-4">
                        See what I can help with <span aria-hidden="true">↓</span>
                    </a>
                </div>
                <aside aria-label="A note about getting started" class="border-l-2 border-emerald-700 pl-6 pb-1 lg:ml-5">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">A good place to start</p>
                    <p class="mt-4 text-2xl font-medium leading-snug tracking-tight">You don’t need to know the technical answer. Just tell me what’s getting in your way.</p>
                </aside>
            </div>
        </section>

        <section id="services" aria-labelledby="services-title" class="border-b border-[#20221f]/15 bg-white/60">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <div class="grid gap-6 border-b border-[#20221f]/20 pb-10 md:grid-cols-[.7fr_1.3fr]">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">What I help with</p>
                    <div>
                        <h2 id="services-title" class="max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">The tech stuff that keeps nagging at you.</h2>
                        <p class="mt-4 max-w-2xl leading-7 text-[#555750]">It might be one stubborn issue or a bigger “where do we even start?” situation. We can take it one piece at a time.</p>
                    </div>
                </div>
                <div class="md:ml-[35%]">
                    <article class="grid gap-2 border-b border-[#20221f]/15 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">A problem that keeps coming back</h3>
                        <p class="leading-7 text-[#555750]">Get help figuring out what’s causing it and what a sensible fix looks like.</p>
                    </article>
                    <article class="grid gap-2 border-b border-[#20221f]/15 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">Systems that make work harder</h3>
                        <p class="leading-7 text-[#555750]">Untangle confusing setups, disconnected tools, and the small frustrations that add up.</p>
                    </article>
                    <article class="grid gap-2 border-b border-[#20221f]/15 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">A decision you don’t want to guess at</h3>
                        <p class="leading-7 text-[#555750]">Talk through options before you spend time or money on a change.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="approach" aria-labelledby="approach-title" class="bg-[#20221f] text-[#f5f3ed]">
            <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 lg:grid-cols-[.8fr_1.2fr] lg:px-8 lg:py-24">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-emerald-300">How I work</p>
                    <h2 id="approach-title" class="mt-5 max-w-md text-3xl font-semibold tracking-tight sm:text-4xl">Plain talk. Useful answers. No pressure.</h2>
                </div>
                <div class="divide-y divide-white/20">
                    <p class="py-5 text-lg leading-8 text-white/80">First, I listen. A clear picture of what’s happening matters more than knowing the right technical terms.</p>
                    <p class="py-5 text-lg leading-8 text-white/80">Then we look at the options together. I’ll explain the trade-offs in everyday language, so you can decide what makes sense.</p>
                    <p class="py-5 text-lg leading-8 text-white/80">The goal is a practical next step you understand—not a bigger, more complicated project than you need.</p>
                </div>
            </div>
        </section>

        <section id="contact" aria-labelledby="contact-title" class="border-b border-[#20221f]/15">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 py-20 sm:grid-cols-[1fr_auto] sm:items-end lg:px-8 lg:py-24">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-emerald-800">Get in touch</p>
                    <h2 id="contact-title" class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">What’s the thing you’d like to make easier?</h2>
                    <p class="mt-4 leading-7 text-[#555750]">A rough description is fine. You don’t need to have it all figured out before starting a conversation.</p>
                </div>
                <p class="border-l-2 border-emerald-700 py-2 pl-4 text-sm text-[#555750]">Contact details are being added.</p>
            </div>
        </section>
    </main>

    <footer class="px-6 py-6 text-center text-sm text-[#686b64]">
        <p>© {{ now()->year }} IT Problem Solver. Practical help with everyday IT.</p>
    </footer>
</body>
</html>
