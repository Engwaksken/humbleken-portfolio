@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'autocomplete' => null,
    'hint' => null,
    'rows' => null,
    'bag' => 'default',
])
@php
    $id = 'field-'.$name;
    $error = $errors->getBag($bag)->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $inputClass = 'rounded border border-[#14263d]/25 bg-white px-3 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784] aria-invalid:border-[#b42318]';
    $current = $type === 'password' ? null : old($name, $value);
@endphp
<div {{ $attributes->class('grid gap-2') }}>
    <label for="{{ $id }}" class="text-sm font-semibold">{{ $label }}</label>
    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-[#506176]">{{ $hint }}</p>
    @endif
    @if ($rows)
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) @if ($required) aria-required="true" @endif
            @if ($error) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="{{ $inputClass }}">{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" @required($required) @if ($required) aria-required="true" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($error) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="{{ $inputClass }}">
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="text-sm text-[#b42318]">{{ $error }}</p>
    @endif
</div>
