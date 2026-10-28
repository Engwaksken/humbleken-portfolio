<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <title>IT Problem Solver — Engineering solutions and technical consulting</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f7fb] text-[#14263d] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-[#14263d] focus:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087e8b]">Skip to content</a>

    <header class="border-b border-[#14263d]/10 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <a href="#home" class="w-fit text-base font-bold tracking-tight text-[#14263d] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" aria-label="IT Problem Solver home">
                IT <span class="text-[#087e8b]">Problem Solver</span>
            </a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium sm:gap-8">
                <a class="transition hover:text-[#087e8b] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" href="#services">Services</a>
                <a class="transition hover:text-[#087e8b] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" href="#approach">Approach</a>
                <a class="font-semibold text-[#087e8b] underline decoration-[#087e8b] decoration-2 underline-offset-4 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        <section id="home" aria-labelledby="hero-title" class="border-b border-[#14263d]/10 bg-gradient-to-br from-white via-white to-[#e8f2f6]">
            <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 sm:py-28 lg:grid-cols-[1.4fr_.6fr] lg:items-end lg:px-8 lg:py-32">
                <div>
                    <p class="mb-7 text-xs font-bold uppercase tracking-[.2em] text-[#087e8b]">IT consulting</p>
                    <h1 id="hero-title" class="max-w-4xl text-5xl font-semibold leading-[1.02] tracking-[-.055em] sm:text-6xl lg:text-7xl">
                        Engineering solutions for <span class="text-[#087e8b]">reliable, effective IT.</span>
                    </h1>
                    <p class="mt-7 max-w-2xl text-lg leading-8 text-[#555750]">Technical consulting to diagnose recurring issues, improve systems, and guide technology decisions. Get clear recommendations and practical engineering support, explained without unnecessary jargon.</p>
                    <a href="#services" class="mt-9 inline-flex items-center gap-3 border-b-2 border-[#087e8b] pb-2 text-sm font-semibold hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]">
                        Explore services <span aria-hidden="true">↓</span>
                    </a>
                </div>
                <aside aria-label="A note about getting started" class="border-l-2 border-[#087e8b] pl-6 pb-1 lg:ml-5">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#087e8b]">Start with the challenge</p>
                    <p class="mt-4 text-2xl font-medium leading-snug tracking-tight">Describe the issue or objective. We can clarify the technical requirements and identify a suitable path forward.</p>
                </aside>
            </div>
        </section>

        <section id="services" aria-labelledby="services-title" class="border-b border-[#14263d]/10 bg-white">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <div class="grid gap-6 border-b border-[#14263d]/15 pb-10 md:grid-cols-[.7fr_1.3fr]">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#087e8b]">Services</p>
                    <div>
                        <h2 id="services-title" class="max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">Technical expertise for systems and decisions.</h2>
                        <p class="mt-4 max-w-2xl leading-7 text-[#506176]">Focused consulting and engineering support to address operational issues, strengthen systems, and make informed technology choices.</p>
                    </div>
                </div>
                <div class="md:ml-[35%]">
                    <article class="grid gap-2 border-b border-[#14263d]/10 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">Recurring technical issues</h3>
                        <p class="leading-7 text-[#506176]">Investigate root causes and evaluate practical options for resolving them.</p>
                    </article>
                    <article class="grid gap-2 border-b border-[#14263d]/10 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">System reliability and effectiveness</h3>
                        <p class="leading-7 text-[#506176]">Assess system setups, disconnected tools, and workflow constraints to identify opportunities for improvement.</p>
                    </article>
                    <article class="grid gap-2 border-b border-[#14263d]/10 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                        <h3 class="text-lg font-semibold">Technology planning and decisions</h3>
                        <p class="leading-7 text-[#506176]">Compare options and trade-offs before committing time or budget to a change.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="approach" aria-labelledby="approach-title" class="bg-[#14263d] text-white">
            <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 lg:grid-cols-[.8fr_1.2fr] lg:px-8 lg:py-24">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#70d0d0]">Approach</p>
                    <h2 id="approach-title" class="mt-5 max-w-md text-3xl font-semibold tracking-tight sm:text-4xl">Clear analysis. Practical recommendations.</h2>
                </div>
                <div class="divide-y divide-white/20">
                    <p class="py-5 text-lg leading-8 text-white/80">Begin by understanding the current environment, the issue, and the outcome you need.</p>
                    <p class="py-5 text-lg leading-8 text-white/80">Assess the options and explain their technical implications and trade-offs in clear language.</p>
                    <p class="py-5 text-lg leading-8 text-white/80">Develop a proportionate path forward focused on effective engineering and reliable systems.</p>
                </div>
            </div>
        </section>

        <section id="contact" aria-labelledby="contact-title" class="border-b border-[#14263d]/10 bg-[#e8f2f6]">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 py-20 sm:grid-cols-[1fr_auto] sm:items-end lg:px-8 lg:py-24">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#087e8b]">Contact</p>
                    <h2 id="contact-title" class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">Discuss your technical requirements.</h2>
                    <p class="mt-4 leading-7 text-[#506176]">Share the issue, objective, or decision you are working through to begin a focused discussion.</p>
                </div>
                <p class="border-l-2 border-[#087e8b] py-2 pl-4 text-sm text-[#506176]">Contact details are being added.</p>
            </div>
        </section>
    </main>

    <footer class="bg-white px-6 py-6 text-center text-sm text-[#506176]">
        <p>© {{ now()->year }} IT Problem Solver. Technical consulting and engineering solutions.</p>
    </footer>
</body>
</html>
