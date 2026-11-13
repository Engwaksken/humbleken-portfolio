<x-layouts.site :title="'Track your booking | '.$brandName" description="Check the status of an appointment request using its reference and your email address.">
    <x-page-hero eyebrow="Appointment request" title="Track your booking." intro="Enter the reference you received and the email address you used to see where your request stands." />

    <section class="container-site section">
        <div class="mx-auto grid max-w-2xl gap-6">
            <form method="POST" action="{{ route('booking.track.lookup') }}" class="card grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:p-8" novalidate>
                @csrf
                @if ($errors->track->any())
                    <p role="alert" class="rounded-xl bg-[#fef3f2] px-4 py-3 text-sm font-medium text-[#b42318] sm:col-span-2">Please correct the highlighted fields and try again.</p>
                @endif
                @foreach ([['reference', 'Booking reference', 'text', 'off', 'e.g. BK-7Q2M4XZA'], ['email', 'Email', 'email', 'email', 'The email you booked with']] as [$field, $label, $type, $autocomplete, $placeholder])
                    <div class="grid content-start gap-2">
                        <label for="track-{{ $field }}" class="label">{{ $label }}</label>
                        <input id="track-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field, request()->input($field)) }}" placeholder="{{ $placeholder }}" required aria-required="true" @error($field, 'track') aria-invalid="true" aria-describedby="track-{{ $field }}-error" @enderror class="input">
                        @error($field, 'track')<p id="track-{{ $field }}-error" class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="sm:col-span-2"><button type="submit" class="btn-primary w-full sm:w-auto">Check status <i class="fa-solid fa-magnifying-glass text-xs" aria-hidden="true"></i></button></div>
            </form>

            <div aria-live="polite">
                @if ($result)
                    <div class="card p-5 sm:p-6" role="status">
                        @include('account.partials.booking-details', ['booking' => $result])
                    </div>
                @elseif ($result === false)
                    <p role="status" class="border-l-2 border-[#b42318] bg-white px-4 py-3 text-sm">We could not find a request with that reference and email. Check both and try again, or <a href="{{ route('contact') }}" class="font-semibold text-brand hover:text-brand-dark">contact us</a>.</p>
                @endif
            </div>

            <p class="text-sm text-muted">
                @auth
                    <a href="{{ route('account.bookings.index') }}" class="font-semibold text-brand hover:text-brand-dark">See all your bookings</a> made with your email address.
                @else
                    Have an account? <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-dark">Sign in</a> to see every request made with your email address.
                @endauth
            </p>
        </div>
    </section>
</x-layouts.site>
