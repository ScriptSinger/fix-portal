@props(['name', 'value' => '', 'uploadUrl', 'destroyUrl', 'rows' => 6])
<div x-data="richEditor({ uploadUrl: @js($uploadUrl), destroyUrl: @js($destroyUrl) })"
    :class="fullscreen ? 'fixed inset-0 z-[60] flex flex-col bg-white p-4' : ''">
    <textarea x-ref="field" name="{{ $name }}" rows="{{ $rows }}" :class="ready ? 'hidden' : ''"
        class="field">{{ old($name, $value) }}</textarea>
    <div x-show="ready" x-cloak class="flex min-h-0 flex-1 flex-col-reverse overflow-hidden rounded-2xl bg-surface">
        <div class="flex flex-wrap items-center gap-1 px-2 py-1">
            <button type="button" @mousedown.prevent @click="toggle('bold')" :class="active.bold ? 'bg-brand-400' : 'hover:bg-black/5'"
                class="size-8 font-bold text-ink" aria-label="Жирный">B</button>
            <button type="button" @mousedown.prevent @click="toggle('underline')" :class="active.underline ? 'bg-brand-400' : 'hover:bg-black/5'"
                class="size-8 font-bold text-ink underline" aria-label="Подчёркнутый">U</button>
            <button type="button" @mousedown.prevent @click="link()" :class="active.link ? 'bg-brand-400' : 'hover:bg-black/5'"
                class="flex size-8 items-center justify-center text-ink" aria-label="Ссылка"><x-ui.icon name="link" class="size-4" /></button>
            <button type="button" @mousedown.prevent @click="pick()" :disabled="busy" class="flex size-8 items-center justify-center text-ink hover:bg-black/5 disabled:opacity-50"
                aria-label="Вставить изображение"><x-ui.icon name="image" class="size-4" /></button>
            <button type="button" @mousedown.prevent @click="fullscreen = !fullscreen" class="ml-auto flex size-8 items-center justify-center text-ink hover:bg-black/5"
                aria-label="Во весь экран"><x-ui.icon name="expand" class="size-4" /></button>
            <input x-ref="file" type="file" accept="image/*" class="hidden" @change="$event.target.files[0] && upload($event.target.files[0])">
        </div>
        <div x-ref="surface" class="min-h-0 flex-1 overflow-y-auto text-[15px]"></div>
    </div>
    <p x-show="error" x-cloak x-text="error" class="mt-1 text-sm text-red-600"></p>
    {{ $slot }}
</div>
