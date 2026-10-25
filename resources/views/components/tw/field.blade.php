@props(['name', 'label', 'type' => 'text', 'value' => null, 'placeholder' => null])
<div class="mb-6">
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'field']) }}
        @error($name) aria-invalid="true" @enderror>
    @error($name)<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
