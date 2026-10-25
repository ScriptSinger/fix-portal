@props(['name', 'label', 'type' => 'text', 'value' => null, 'placeholder' => null])
<div class="mb-6">
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'block h-[38px] w-full border border-[#ced4da] bg-white px-3 text-base text-[#464a4c] placeholder:text-[#a3a5a8] focus:border-brand-500 focus:outline-none']) }}
        @error($name) aria-invalid="true" @enderror>
    @error($name)<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
