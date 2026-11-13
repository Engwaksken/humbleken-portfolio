<ul class="divide-y divide-ink/10 border-y border-line bg-white">
    @foreach ($bookings as $booking)
        <li class="px-5 py-4">@include('account.partials.booking-details')</li>
    @endforeach
</ul>
