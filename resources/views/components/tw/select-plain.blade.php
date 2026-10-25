@props(['name', 'label', 'options' => [], 'value' => null, 'blank' => 'Все'])
<div>
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <select id="f-{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'block h-[38px] w-full border border-[#ced4da] bg-white px-2 text-[15px] text-body focus:border-brand-500 focus:outline-none']) }}>
        @if ($blank !== null)<option value="">{{ $blank }}</option>@endif
        @foreach ($options as $key => $title)
            <option value="{{ is_int($key) ? $title : $key }}" @selected((string) $value === (string) (is_int($key) ? $title : $key))>{{ $title }}</option>
        @endforeach
    </select>
</div>
