@props(['name', 'label', 'options' => [], 'value' => null, 'blank' => 'Все'])
<div>
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <select id="f-{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'field']) }}>
        @if ($blank !== null)<option value="">{{ $blank }}</option>@endif
        @foreach ($options as $key => $title)
            <option value="{{ is_int($key) ? $title : $key }}" @selected((string) $value === (string) (is_int($key) ? $title : $key))>{{ $title }}</option>
        @endforeach
    </select>
</div>
