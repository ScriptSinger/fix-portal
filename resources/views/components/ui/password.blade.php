@props(['name', 'label', 'autocomplete' => 'current-password', 'aside' => null])
{{-- Поле пароля с кнопкой «показать» --}}
<div class="mb-6" x-data="{ shown: false }">
    <div class="mb-2 flex items-baseline justify-between gap-3">
        <label for="f-{{ $name }}">{{ $label }}</label>
        {{ $aside }}
    </div>
    <div class="relative">
        <input id="f-{{ $name }}" name="{{ $name }}" :type="shown ? 'text' : 'password'" autocomplete="{{ $autocomplete }}" required
            class="field !h-12 pr-12"
            @error($name) aria-invalid="true" aria-describedby="e-{{ $name }}" @enderror>
        <button type="button" @click="shown = !shown" :aria-label="shown ? 'Скрыть пароль' : 'Показать пароль'" :aria-pressed="shown"
            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-subtle hover:text-black">
            <x-ui.icon name="eye" class="size-5" />
        </button>
    </div>
    @error($name)<p id="e-{{ $name }}" class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
