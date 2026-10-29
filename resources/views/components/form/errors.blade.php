@props(['bag' => 'default'])
@if ($errors->getBag($bag)->any())
    <p role="alert" {{ $attributes->class('text-sm font-medium text-[#b42318]') }}>Please correct the highlighted fields and try again.</p>
@endif
