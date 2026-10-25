@props(['name', 'label', 'options' => [], 'value' => null])
<div class="mb-6">
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <select id="f-{{ $name }}" name="{{ $name }}"
        class="field">
        @foreach ($options as $key => $title)
            <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>{{ $title }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
