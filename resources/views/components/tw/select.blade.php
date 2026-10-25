@props(['name', 'label', 'options' => [], 'value' => null])
<div class="mb-6">
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <select id="f-{{ $name }}" name="{{ $name }}"
        class="block h-[38px] w-full border border-[#ced4da] bg-white px-3 text-base text-[#464a4c] focus:border-brand-500 focus:outline-none">
        @foreach ($options as $key => $title)
            <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>{{ $title }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
