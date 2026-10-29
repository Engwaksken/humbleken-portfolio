<x-layouts.site :title="$brandName.' — '.$tagline">
    <section id="home" aria-labelledby="hero-title" class="border-b border-[#14263d]/10 bg-gradient-to-br from-white via-white to-[#e8f2f6]">
        <div class="mx-auto grid max-w-6xl gap-12 px-6 py-20 sm:py-28 lg:grid-cols-[1.4fr_.6fr] lg:items-end lg:px-8 lg:py-32">
            <div>
                <p class="mb-7 text-xs font-bold uppercase tracking-[.2em] text-[#077784]">IT consulting</p>
                <h1 id="hero-title" class="max-w-4xl text-5xl font-semibold leading-[1.02] tracking-[-.055em] sm:text-6xl lg:text-7xl">
                    Engineering solutions for <span class="text-[#077784]">reliable, effective IT.</span>
                </h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-[#555750]">Technical consulting to diagnose recurring issues, improve systems, and guide technology decisions. Get clear recommendations and practical engineering support, explained without unnecessary jargon.</p>
                <a href="#services" class="mt-9 inline-flex items-center gap-3 border-b-2 border-[#077784] pb-2 text-sm font-semibold hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">
                    Explore services <span aria-hidden="true">↓</span>
                </a>
            </div>
            <aside aria-label="A note about getting started" class="border-l-2 border-[#077784] pl-6 pb-1 lg:ml-5">
                <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#077784]">Start with the challenge</p>
                <p class="mt-4 text-2xl font-medium leading-snug tracking-tight">Describe the issue or objective. We can clarify the technical requirements and identify a suitable path forward.</p>
            </aside>
        </div>
    </section>

    <section id="services" aria-labelledby="services-title" class="border-b border-[#14263d]/10 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
            <div class="grid gap-6 border-b border-[#14263d]/15 pb-10 md:grid-cols-[.7fr_1.3fr]">
                <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#077784]">Services</p>
                <div>
                    <h2 id="services-title" class="max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">Technical expertise for systems and decisions.</h2>
                    <p class="mt-4 max-w-2xl leading-7 text-[#506176]">Focused consulting and engineering support to address operational issues, strengthen systems, and make informed technology choices.</p>
                </div>
            </div>
            <div class="md:ml-[35%]">
                @if ($services->isNotEmpty())
                    @foreach ($serviceCategories as $category)
                        @if ($servicesByCategory->has($category->id))
                            <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#077784]">{{ $category->name }}</p>
                            @foreach ($servicesByCategory[$category->id] as $service)
                                <article class="grid gap-2 border-b border-[#14263d]/10 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                                    <h3 class="text-lg font-semibold">{{ $service->name }}</h3>
                                    <p class="leading-7 text-[#506176]">{{ $service->summary ?: $service->description }}</p>
                                </article>
                            @endforeach
                        @endif
                    @endforeach
                    @foreach ($ungroupedServices as $service)
                        <article class="grid gap-2 border-b border-[#14263d]/10 py-7 sm:grid-cols-[.8fr_1.2fr] sm:gap-8">
                            <h3 class="text-lg font-semibold">{{ $service->name }}</h3>
                            <p class="leading-7 text-[#506176]">{{ $service->summary ?: $service->description }}</p>
                        </article>
                    @endforeach
                @else
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
                @endif
            </div>
        </div>
    </section>

    @if ($featuredProjects->isNotEmpty())
        <section id="projects" aria-labelledby="projects-title" class="border-b border-[#14263d]/10">
            <div class="mx-auto max-w-6xl px-6 py-20 lg:px-8 lg:py-24">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#077784]">Projects</p>
                        <h2 id="projects-title" class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">Recent work.</h2>
                    </div>
                    <a href="{{ route('projects.index') }}" class="w-fit text-sm font-semibold text-[#077784] underline underline-offset-4 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">View all projects</a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featuredProjects as $project)
                        @include('projects.partials.card')
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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
                <p class="text-sm font-semibold uppercase tracking-[.16em] text-[#077784]">Contact</p>
                <h2 id="contact-title" class="mt-5 text-3xl font-semibold tracking-tight sm:text-4xl">Discuss your technical requirements.</h2>
                <p class="mt-4 leading-7 text-[#506176]">Share the issue, objective, or decision you are working through to begin a focused discussion.</p>
            </div>
            @if (!$hasContact)
                <p class="border-l-2 border-[#077784] py-2 pl-4 text-sm text-[#506176]">Contact details are being added.</p>
            @else
                <ul class="border-l-2 border-[#077784] py-2 pl-4 text-sm text-[#506176]">
                    @if ($contactEmail)
                        <li><a href="mailto:{{ $contactEmail }}" class="text-[#077784] underline decoration-[#077784]/60 underline-offset-2 transition hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">{{ $contactEmail }}</a></li>
                    @endif
                    @if ($contactPhone)
                        <li><a href="tel:{{ $contactPhone }}" class="text-[#077784] underline decoration-[#077784]/60 underline-offset-2 transition hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">{{ $contactPhone }}</a></li>
                    @endif
                    @if ($whatsapp)
                        <li><a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer" class="text-[#077784] underline decoration-[#077784]/60 underline-offset-2 transition hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">WhatsApp {{ $whatsapp }}</a></li>
                    @endif
                    @if ($address)
                        <li>{{ $address }}</li>
                    @endif
                    @if ($businessHours)
                        <li>{{ $businessHours }}</li>
                    @endif
                    @foreach ($socialLinks as $platform => $url)
                        <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-[#077784] underline decoration-[#077784]/60 underline-offset-2 transition hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">{{ ucfirst($platform) }}</a></li>
                    @endforeach
                </ul>
            @endif

            <div class="sm:col-span-2">
                @if (session('enquiry_sent'))
                    <p role="status" class="border-l-2 border-[#077784] bg-white px-4 py-3 text-sm font-medium">Thank you. Your enquiry has been received and will be reviewed shortly.</p>
                @else
                    <form method="POST" action="{{ route('enquiries.store') }}" class="grid gap-5 bg-white p-6 sm:grid-cols-2 sm:p-8" novalidate>
                        @csrf

                        @if ($errors->enquiry->any())
                            <p role="alert" class="text-sm font-medium text-[#b42318] sm:col-span-2">Please correct the highlighted fields and try again.</p>
                        @endif

                        @foreach ([
                            ['name', 'Name', 'text', 'name', true],
                            ['email', 'Email', 'email', 'email', true],
                            ['phone', 'Phone (optional)', 'tel', 'tel', false],
                        ] as [$field, $label, $type, $autocomplete, $required])
                            <div class="grid gap-2">
                                <label for="enquiry-{{ $field }}" class="text-sm font-semibold">{{ $label }}</label>
                                <input id="enquiry-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" @if ($required) required aria-required="true" @endif
                                    @error($field, 'enquiry') aria-invalid="true" aria-describedby="enquiry-{{ $field }}-error" @enderror
                                    class="rounded border border-[#14263d]/25 px-3 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784] aria-invalid:border-[#b42318]">
                                @error($field, 'enquiry')
                                    <p id="enquiry-{{ $field }}-error" class="text-sm text-[#b42318]">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        @if ($services->isNotEmpty())
                            <div class="grid gap-2">
                                <label for="enquiry-service_id" class="text-sm font-semibold">Service (optional)</label>
                                <select id="enquiry-service_id" name="service_id"
                                    @error('service_id', 'enquiry') aria-invalid="true" aria-describedby="enquiry-service_id-error" @enderror
                                    class="rounded border border-[#14263d]/25 px-3 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">
                                    <option value="">Not sure yet</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_id', 'enquiry')
                                    <p id="enquiry-service_id-error" class="text-sm text-[#b42318]">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <div class="grid gap-2 sm:col-span-2">
                            <label for="enquiry-message" class="text-sm font-semibold">How can we help?</label>
                            <textarea id="enquiry-message" name="message" rows="5" required aria-required="true"
                                @error('message', 'enquiry') aria-invalid="true" aria-describedby="enquiry-message-error" @enderror
                                class="rounded border border-[#14263d]/25 px-3 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784] aria-invalid:border-[#b42318]">{{ old('message') }}</textarea>
                            @error('message', 'enquiry')
                                <p id="enquiry-message-error" class="text-sm text-[#b42318]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="hidden" aria-hidden="true">
                            <label for="enquiry-website">Leave this field empty</label>
                            <input id="enquiry-website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="rounded bg-[#077784] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#075d68] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">Send enquiry</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
