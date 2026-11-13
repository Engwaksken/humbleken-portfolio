@php
    $contactRow = 'flex items-start gap-4 py-2 transition hover:text-brand-dark';
    $iconBox = 'w-6 shrink-0 pt-2.5 text-center text-lg text-brand';
@endphp
<x-layouts.site :title="$page['title'].' | '.$brandName" :description="$page['intro']">
    <x-page-hero :eyebrow="$page['eyebrow']" :title="$page['title']" :intro="$page['intro']" :intro-lines="$page['intro_lines']" :image="$page['image_url']" :image-alt="$page['image_alt']" />

    <section class="container-site section">
        <div class="grid gap-layout lg:grid-cols-12">
            <aside class="lg:col-span-4" aria-label="Contact details">
                <div class="card space-y-1 p-5 sm:p-6 lg:sticky lg:top-24">
                    <h2 class="mb-4 text-xs font-bold tracking-[.14em] text-muted uppercase">Get in touch</h2>
                    @if ($contactEmail)
                        <a href="mailto:{{ $contactEmail }}" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span><span class="min-w-0"><span class="block text-xs text-muted">Email</span><span class="block font-semibold break-all text-brand-dark">{{ $contactEmail }}</span></span></a>
                    @endif
                    @if ($contactPhone)
                        <a href="tel:{{ $contactPhone }}" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><i class="fa-solid fa-phone"></i></span><span><span class="block text-xs text-muted">Phone</span><span class="block font-semibold text-brand-dark">{{ $contactPhone }}</span></span></a>
                    @endif
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer" class="{{ $contactRow }}"><span class="{{ $iconBox }}" aria-hidden="true"><i class="fa-solid fa-comment-dots"></i></span><span><span class="block text-xs text-muted">WhatsApp <span class="sr-only">(opens in a new tab)</span></span><span class="block font-semibold text-brand-dark">WhatsApp {{ $whatsapp }}</span></span></a>
                    @endif
                    @if ($address)
                        <div class="{{ $contactRow }} hover:bg-transparent"><span class="{{ $iconBox }}" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span><span><span class="block text-xs text-muted">Address</span><span class="block font-medium">{{ $address }}</span></span></div>
                    @endif
                    @if ($businessHours)
                        <div class="{{ $contactRow }} hover:bg-transparent"><span class="{{ $iconBox }}" aria-hidden="true"><i class="fa-solid fa-clock"></i></span><span><span class="block text-xs text-muted">Business hours</span><span class="block font-medium">{{ $businessHours }}</span></span></div>
                    @endif
                    @if (! empty($socialLinks))
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-line pt-5">
                            @foreach ($socialLinks as $platform => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="chip hover:bg-brand hover:text-white">{{ ucfirst($platform) }}<span class="sr-only"> (opens in a new tab)</span></a>
                            @endforeach
                        </div>
                    @endif
                    @if (! $contactEmail && ! $contactPhone && ! $whatsapp && ! $address)
                        <p class="text-sm leading-6 text-muted">Use the form to send me an enquiry and I will reply as soon as I can.</p>
                    @endif
                </div>
            </aside>

            <div class="lg:col-span-8">
                @if (session('enquiry_sent'))
                    <div role="status" class="card flex items-start gap-4 border-brand/40 p-5 sm:p-8">
                        <i class="fa-solid fa-circle-check pt-0.5 text-2xl text-brand" aria-hidden="true"></i>
                        <p class="font-medium">Thank you. Your enquiry has been received and will be reviewed shortly.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('enquiries.store') }}" class="card grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:p-8" novalidate>
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
                                <input id="enquiry-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" placeholder="{{ $field === 'phone' ? 'Enter your phone number' : 'Enter your '.strtolower($label) }}" @if($required) required aria-required="true" @endif @error($field,'enquiry') aria-invalid="true" aria-describedby="enquiry-{{ $field }}-error" @enderror class="input">
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
                            <label for="enquiry-message" class="label">How can I help?</label>
                            <textarea id="enquiry-message" name="message" rows="5" placeholder="Describe what you need help with" required aria-required="true" @error('message','enquiry') aria-invalid="true" aria-describedby="enquiry-message-error" @enderror class="input">{{ old('message') }}</textarea>
                            @error('message','enquiry')<p id="enquiry-message-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                        </div>
                        <div class="hidden" aria-hidden="true"><label for="enquiry-website">Leave this field empty</label><input id="enquiry-website" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="sm:col-span-2"><button type="submit" class="btn-primary w-full sm:w-auto">Send enquiry <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></button></div>
                    </form>
                @endif
            </div>
        </div>
    </section>

    <x-page-extras :page="$page" />
</x-layouts.site>
