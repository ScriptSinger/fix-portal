@props(['name', 'label', 'autocomplete' => 'current-password'])
{{-- Поле пароля с кнопкой «показать» --}}
<div class="mb-6" x-data="{ shown: false }">
    <label for="f-{{ $name }}" class="mb-2 block">{{ $label }}</label>
    <div class="relative">
        <input id="f-{{ $name }}" name="{{ $name }}" :type="shown ? 'text' : 'password'" autocomplete="{{ $autocomplete }}" required
            @class(['block h-[38px] w-full border bg-white pl-3 pr-11 text-base text-[#464a4c] focus:border-brand-500 focus:outline-none', 'border-[#ced4da]' => ! $errors->has($name), 'border-red-600' => $errors->has($name)])
            @error($name) aria-invalid="true" aria-describedby="e-{{ $name }}" @enderror>
        <button type="button" @click="shown = !shown" :aria-label="shown ? 'Скрыть пароль' : 'Показать пароль'" :aria-pressed="shown"
            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-[#6b6b6b] hover:text-black">
            <x-tw.icon name="eye" class="size-5" />
        </button>
    </div>
    @error($name)<p id="e-{{ $name }}" class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
</div>
