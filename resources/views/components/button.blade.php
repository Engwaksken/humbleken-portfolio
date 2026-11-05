<button {{ $attributes->merge(['type' => 'submit'])->class('btn-primary w-full sm:w-fit') }}>{{ $slot }}</button>
