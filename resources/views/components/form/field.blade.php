@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'autocomplete' => null,
    'placeholder' => null,
    'hint' => null,
    'rows' => null,
    'bag' => 'default',
])
@php
    $id = 'field-'.$name;
    $error = $errors->getBag($bag)->first($name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $inputClass = 'input';
    $current = $type === 'password' ? null : old($name, $value);
    $placeholderText = $placeholder ?? 'Enter '.strtolower($label);
@endphp
<div {{ $attributes->class('grid gap-2') }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    @if ($hint)
        <p id="{{ $id }}-hint" class="text-sm text-muted">{{ $hint }}</p>
    @endif
    @if ($rows)
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholderText }}" @required($required) @if ($required) aria-required="true" @endif
            @if ($error) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="{{ $inputClass }}">{{ $current }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}" placeholder="{{ $placeholderText }}" @required($required) @if ($required) aria-required="true" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($error) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="{{ $inputClass }}">
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="text-sm text-[#b42318]">{{ $error }}</p>
    @endif
</div>
