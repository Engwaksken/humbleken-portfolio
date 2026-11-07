<x-layouts.site :title="$pageTitle.' — '.$brandName" :description="$pageIntro">
    <x-page-hero eyebrow="Appointment request" :title="$pageTitle" :intro="$pageIntro" />

    <section class="container-site section">
        <div class="grid gap-layout lg:grid-cols-12">
            <div class="lg:col-span-8">
                @if (session('booking_sent'))
                    <div role="status" class="card flex items-start gap-4 border-brand/40 p-5 sm:p-8">
                        <i class="fa-solid fa-circle-check pt-0.5 text-2xl text-brand" aria-hidden="true"></i>
                        <p class="font-medium">Thank you. Your appointment request has been received.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('booking-requests.store') }}" class="card grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:p-8" novalidate>
                        @csrf
                        @if ($errors->booking->any())
                            <p role="alert" class="rounded-xl bg-[#fef3f2] px-4 py-3 text-sm font-medium text-[#b42318] sm:col-span-2">Please correct the highlighted fields and try again.</p>
                        @endif
                        @foreach ([['name','Name','text','name'],['email','Email','email','email'],['phone','Phone (optional)','tel','tel']] as [$field,$label,$type,$autocomplete])
                            <div class="grid content-start gap-2">
                                <label for="booking-{{ $field }}" class="label">{{ $label }}</label>
                                <input id="booking-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" placeholder="{{ $field === 'phone' ? 'Enter your phone number' : 'Enter your '.strtolower($label) }}" @if($field !== 'phone') required aria-required="true" @endif @error($field,'booking') aria-invalid="true" aria-describedby="booking-{{ $field }}-error" @enderror class="input">
                                @error($field,'booking')<p id="booking-{{ $field }}-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div class="grid content-start gap-2">
                            <label for="booking-preferred_at" class="label">Preferred date and time</label>
                            <input id="booking-preferred_at" name="preferred_at" type="datetime-local" value="{{ old('preferred_at') }}" required aria-required="true" @error('preferred_at','booking') aria-invalid="true" aria-describedby="booking-preferred_at-error" @enderror class="input">
                            @error('preferred_at','booking')<p id="booking-preferred_at-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                        </div>
                        @if ($services->isNotEmpty())
                            <div class="grid content-start gap-2 sm:col-span-2">
                                <label for="booking-service_id" class="label">Service (optional)</label>
                                <select id="booking-service_id" name="service_id" class="input">
                                    <option value="">Not sure yet</option>
                                    @foreach ($services as $service)<option value="{{ $service->id }}" @selected((string) old('service_id', request('service')) === (string) $service->id)>{{ $service->name }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <div class="grid gap-2 sm:col-span-2">
                            <label for="booking-message" class="label">What would you like to discuss? (optional)</label>
                            <textarea id="booking-message" name="message" rows="4" placeholder="Tell us what you would like to discuss" class="input">{{ old('message') }}</textarea>
                        </div>
                        <div class="hidden" aria-hidden="true"><label for="booking-website">Leave this field empty</label><input id="booking-website" name="website" tabindex="-1" autocomplete="off"></div>
                        <div class="sm:col-span-2"><button type="submit" class="btn-primary w-full sm:w-auto">Request appointment <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></button></div>
                    </form>
                @endif
            </div>
            <aside class="lg:col-span-4" aria-label="What happens next">
                <div class="card p-5 sm:p-6 lg:sticky lg:top-24">
                    <h2 class="text-xs font-bold tracking-[.14em] text-muted uppercase">What happens next</h2>
                    <ol class="mt-5 space-y-5">
                        @foreach (['We review your request and preferred time.', 'We contact you to confirm availability.', 'We meet to discuss your requirements.'] as $i => $step)
                            <li class="flex gap-3">
                                <span class="w-5 shrink-0 text-sm font-bold text-brand" aria-hidden="true">{{ $i + 1 }}.</span>
                                <span class="text-sm leading-6">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
