@php
    $contactRow = 'flex items-start gap-4 rounded-xl p-3 -mx-3 transition hover:bg-brand-tint/60';
    $iconBox = 'grid size-10 shrink-0 place-items-center rounded-xl bg-brand-tint text-brand-dark';
@endphp
<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <x-page-hero eyebrow="Contact" :title="$pageTitle" :intro="$pageIntro" />

    <section class="container-site py-12 sm:py-16">
        <div class="grid gap-8 lg:grid-cols-12 lg:gap-12">
            <aside class="lg:col-span-4" aria-label="Contact details">
                <div class="card space-y-1 p-6 lg:sticky lg:top-28">
                    <h2 class="mb-4 text-xs font-bold tracking-[.14em] text-muted uppercase">Get in touch</h2>
                    @if ($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="min-w-0"><span class="block text-xs text-muted">Email</span><span class="block font-semibold break-all text-brand-dark">{{ $contactEmail }}</span></span></a>
                    @endif
                    @if ($contactPhone)
                        <a href="tel:{{ $contactPhone }}" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" stroke-linejoin="round"/></svg></span><span><span class="block text-xs text-muted">Phone</span><span class="block font-semibold text-brand-dark">{{ $contactPhone }}</span></span></a>
                    @endif
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.5-4.2A8 8 0 1 1 20 11.5Z" stroke-linejoin="round"/></svg></span><span><span class="block text-xs text-muted">WhatsApp <span class="sr-only">(opens in a new tab)</span></span><span class="block font-semibold text-brand-dark">WhatsApp {{ $whatsapp }}</span></span></a>
                    @endif
                    @if ($address)
                        <div class="{{ $contactRow }} hover:bg-transparent"><span class="{{ $iconBox }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg></span><span><span class="block text-xs text-muted">Address</span><span class="block font-medium">{{ $address }}</span></span></div>
                    @endif
                    @if ($businessHours)
                        <div class="{{ $contactRow }} hover:bg-transparent"><span class="{{ $iconBox }}" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round"/></svg></span><span><span class="block text-xs text-muted">Business hours</span><span class="block font-medium">{{ $businessHours }}</span></span></div>
                    @endif
                    @if (! empty($socialLinks))
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-5">
                            @foreach ($socialLinks as $platform => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="chip hover:bg-brand hover:text-white">{{ ucfirst($platform) }}<span class="sr-only"> (opens in a new tab)</span></a>
                            @endforeach
                        </div>
                    @endif
                    @if (! $contactEmail && ! $contactPhone && ! $whatsapp && ! $address)
                        <p class="text-sm leading-6 text-muted">Use the form to send an enquiry and we will respond as soon as possible.</p>
                    @endif
                </div>
            </aside>

            <div class="lg:col-span-8">
                @if (session('enquiry_sent'))
                    <div role="status" class="card flex items-start gap-4 border-brand/40 p-6 sm:p-8">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand text-white" aria-hidden="true"><svg viewBox="0 0 24 24" class="size-5 fill-none stroke-current" stroke-width="2.5"><path d="M5 12l4 4 10-10" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <p class="pt-2 font-medium">Thank you. Your enquiry has been received and will be reviewed shortly.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('enquiries.store') }}" class="card grid gap-5 p-6 sm:grid-cols-2 sm:p-8" novalidate>
                        @csrf
                        <div class="sm:col-span-2">
                            <h2 class="text-xl font-semibold">Send an enquiry</h2>
                            <p class="mt-1 text-sm text-muted">Fields marked optional can be left blank.</p>
                        </div>
                        @if ($errors->enquiry->any())
                            <p role="alert" class="rounded-xl bg-[#fef3f2] px-4 py-3 text-sm font-medium text-[#b42318] sm:col-span-2">Please correct the highlighted fields and try again.</p>
                        @endif
                        @foreach ([['name','Name','text','name',true],['email','Email','email','email',true],['phone','Phone (optional)','tel','tel',false]] as [$field,$label,$type,$autocomplete,$required])
                            <div class="grid content-start gap-2">
                                <label for="enquiry-{{ $field }}" class="label">{{ $label }}</label>
                                <input id="enquiry-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" placeholder="{{ $field === 'phone' ? 'Enter your phone number (optional)' : 'Enter your '.strtolower($label) }}" @if($required) required aria-required="true" @endif @error($field,'enquiry') aria-invalid="true" aria-describedby="enquiry-{{ $field }}-error" @enderror class="input">
                                @error($field,'enquiry')<p id="enquiry-{{ $field }}-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        @if ($services->isNotEmpty())
                            <div class="grid content-start gap-2">
                                <label for="enquiry-service_id" class="label">Service (optional)</label>
                                <select id="enquiry-service_id" name="service_id" class="input">
                                    <option value="">Not sure yet</option>
                                    @foreach ($services as $service)<option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->name }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <div class="grid gap-2 sm:col-span-2">
                            <label for="enquiry-message" class="label">How can we help?</label>
                            <textarea id="enquiry-message" name="message" rows="5" placeholder="Describe what you need help with" required aria-required="true" @error('message','enquiry') aria-invalid="true" aria-describedby="enquiry-message-error" @enderror class="input">{{ old('message') }}</textarea>
                            @error('message','enquiry')<p id="enquiry-message-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                        </div>
                        <div class="hidden" aria-hidden="true"><label for="enquiry-website">Leave this field empty</label><input id="enquiry-website" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="sm:col-span-2"><button type="submit" class="btn-primary w-full sm:w-auto">Send enquiry <span aria-hidden="true">→</span></button></div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</x-layouts.site>
