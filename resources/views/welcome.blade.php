<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f7f5">
    <title>SmartEngineer — Reliable IT, made simple</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-slate-900 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-slate-900 focus:shadow-lg">Skip to content</a>

    <header class="border-b border-slate-200/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-10">
            <a href="#home" class="text-lg font-bold tracking-tight" aria-label="SmartEngineer home">
                Smart<span class="text-teal-700">Engineer</span>
            </a>
            <nav aria-label="Main navigation" class="flex items-center gap-5 text-sm font-medium sm:gap-8">
                <a class="transition hover:text-teal-700 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700" href="#services">Services</a>
                <a class="transition hover:text-teal-700 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700" href="#approach">Approach</a>
                <a class="rounded-full bg-slate-900 px-4 py-2.5 text-white transition hover:bg-teal-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700" href="#contact">Get in touch</a>
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        <section id="home" aria-labelledby="hero-title" class="relative overflow-hidden">
            <div aria-hidden="true" class="absolute -right-32 -top-36 h-[34rem] w-[34rem] rounded-full bg-teal-100/70 blur-3xl"></div>
            <div class="relative mx-auto grid max-w-7xl gap-14 px-6 py-24 md:py-32 lg:grid-cols-[1.2fr_.8fr] lg:items-center lg:px-10 lg:py-36">
                <div>
                    <p class="mb-6 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[.16em] text-teal-800">
                        <span aria-hidden="true" class="h-2 w-2 rounded-full bg-teal-600"></span>
                        Practical technology. Thoughtfully delivered.
                    </p>
                    <h1 id="hero-title" class="max-w-3xl text-5xl font-semibold leading-[1.06] tracking-[-.045em] sm:text-6xl lg:text-7xl">
                        Turning Complex IT Challenges into <span class="text-teal-800">Simple, Reliable Solutions.</span>
                    </h1>
                    <p class="mt-7 max-w-xl text-lg leading-8 text-slate-600">Technology should make work easier, not add another layer of complexity. SmartEngineer helps you find a clear path from a challenge to a dependable solution.</p>
                    <div class="mt-9 flex flex-wrap gap-4">
                        <a href="#contact" class="inline-flex items-center gap-2 rounded-full bg-teal-800 px-6 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700">Start a conversation <span aria-hidden="true">→</span></a>
                        <a href="#services" class="inline-flex items-center rounded-full border border-slate-300 bg-white/60 px-6 py-3.5 text-sm font-semibold transition hover:border-slate-500 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-700">Explore services</a>
                    </div>
                </div>
                <div aria-label="Technology made clear: understand, simplify, strengthen" class="rounded-[2rem] border border-slate-200 bg-white/75 p-7 shadow-[0_24px_80px_-40px_rgba(15,23,42,.3)] sm:p-10">
                    <p class="text-xs font-semibold uppercase tracking-[.2em] text-slate-500">A clearer way forward</p>
                    <ol class="mt-8 space-y-7">
                        <li class="flex gap-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-sm font-bold text-teal-800">01</span>
                            <div>
                                <h2 class="font-semibold">Understand the challenge</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Start with your goals, constraints, and the people who rely on your systems.</p>
                            </div>
                        </li>
                        <li class="flex gap-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-800">02</span>
                            <div>
                                <h2 class="font-semibold">Make complexity manageable</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Turn the problem into practical options and a clear plan.</p>
                            </div>
                        </li>
                        <li class="flex gap-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">03</span>
                            <div>
                                <h2 class="font-semibold">Build for reliability</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Focus on solutions that are understandable, maintainable, and fit for purpose.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <section id="services" aria-labelledby="services-title" class="border-y border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-10 lg:py-24">
                <div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[.18em] text-teal-800">What we can help with</p><h2 id="services-title" class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Technology that works for your business.</h2><p class="mt-4 leading-7 text-slate-600">From sorting out a specific IT issue to planning a more dependable foundation, the focus stays on what is useful for you.</p></div>
                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 p-7"><span aria-hidden="true" class="text-2xl text-teal-800">↗</span><h3 class="mt-5 text-lg font-semibold">IT problem-solving</h3><p class="mt-3 text-sm leading-6 text-slate-600">Get a clearer picture of technical challenges and practical ways to address them.</p></article>
                    <article class="rounded-2xl border border-slate-200 p-7"><span aria-hidden="true" class="text-2xl text-teal-800">⌘</span><h3 class="mt-5 text-lg font-semibold">Systems &amp; infrastructure</h3><p class="mt-3 text-sm leading-6 text-slate-600">Consider how your systems fit together, where friction exists, and what could be simpler.</p></article>
                    <article class="rounded-2xl border border-slate-200 p-7"><span aria-hidden="true" class="text-2xl text-teal-800">◎</span><h3 class="mt-5 text-lg font-semibold">Reliability &amp; improvement</h3><p class="mt-3 text-sm leading-6 text-slate-600">Identify sensible improvements that support consistent, maintainable day-to-day operations.</p></article>
                </div>
            </div>
        </section>

        <section id="approach" aria-labelledby="approach-title" class="mx-auto grid max-w-7xl gap-10 px-6 py-20 lg:grid-cols-2 lg:items-center lg:px-10 lg:py-24">
            <div><p class="text-sm font-semibold uppercase tracking-[.18em] text-teal-800">Our approach</p><h2 id="approach-title" class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Clear thinking, without the unnecessary jargon.</h2></div>
            <div class="space-y-5 text-base leading-7 text-slate-600"><p>Good solutions begin with listening. We take time to understand what is happening, what matters most, and what a successful outcome looks like.</p><p>Then we work through the options in plain language, keeping recommendations grounded in your needs. The aim is progress you can understand and rely on—not complexity for its own sake.</p></div>
        </section>

        <section id="contact" aria-labelledby="contact-title" class="bg-slate-900 text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-8 px-6 py-16 sm:flex-row sm:items-center sm:justify-between lg:px-10 lg:py-20">
                <div class="max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[.18em] text-teal-300">Contact</p><h2 id="contact-title" class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">Let’s make your next IT challenge simpler.</h2><p class="mt-4 leading-7 text-slate-300">Have a question or a problem to untangle? Reach out to start a conversation.</p></div>
                <p class="shrink-0 rounded-xl border border-slate-700 px-5 py-4 text-sm text-slate-300">Contact details coming soon.</p>
            </div>
        </section>
    </main>

    <footer class="bg-slate-950 px-6 py-6 text-center text-sm text-slate-400">
        <p>© {{ now()->year }} SmartEngineer. Thoughtful solutions for a more reliable IT experience.</p>
    </footer>
</body>
</html>
