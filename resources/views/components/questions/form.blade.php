{{-- Общая форма вопроса (создание и правка), оформлена как черновик поста в Telegram: пузырь с заголовком, плашками прибора и модели, текстом и фото, внизу кнопка отправки.
     Подсказки направляют к заголовку с симптомом: по нему вопрос находят в поиске --}}
@props(['action', 'question' => null, 'appliances', 'method' => null, 'collapsed' => false])
@php
    $pill = 'h-9 rounded-full border-0 bg-black/5 px-4 text-sm leading-9 text-ink placeholder:text-subtle focus:outline-none focus:ring-2 focus:ring-brand-500';
    $plain = \App\Support\MessageBody::toPlain(\App\Support\ContentImages::extract($question?->description)[0]);
    $initial = $question ? array_column(\App\Support\ContentImages::extract($question->description)[1], 'full') : [];
    // при ошибке проверки возвращаем загруженные фото из old()
    $initial = old('images', $initial);
@endphp
<form method="POST" action="{{ $action }}" @submit="sending = true"
    x-data="{ ...photoAttach({ uploadUrl: @js(route('api.users.images.upload')), destroyUrl: @js(route('api.users.images.destroy', ['image' => ':id'])), initial: @js($initial) }), open: @js(! $collapsed || $errors->any()) }"
    x-init="if (location.hash === '#ask') { open = true; $nextTick(() => $refs.title.focus()) }">
    @csrf
    @if ($method) @method($method) @endif

    <div class="overflow-hidden rounded-2xl bg-white shadow-sm" :class="open || 'ask-pulse ring-2 ring-brand-400'">
        <fieldset x-show="open" x-cloak class="m-0 flex flex-wrap gap-2 border-0 px-4 pt-4 sm:px-5">
            <legend class="sr-only">Тип прибора</legend>
            @foreach ($appliances as $key => $title)
                <label class="cursor-pointer">
                    <input type="radio" name="appliance_id" value="{{ $key }}" class="peer sr-only" @required($loop->first) @checked((string) old('appliance_id', $question?->appliance_id) === (string) $key)>
                    <span class="inline-flex h-9 items-center rounded-full bg-black/5 px-4 text-sm font-medium text-ink peer-checked:bg-black peer-checked:text-brand-400 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500">{{ $title }}</span>
                </label>
            @endforeach
        </fieldset>
        <div x-show="open" x-cloak class="flex flex-wrap gap-2 px-4 pt-2 sm:px-5">
            <label for="f-model" class="sr-only">Марка и модель</label>
            <input id="f-model" name="model" type="text" value="{{ old('model', $question?->model) }}" placeholder="Марка и модель" class="{{ $pill }} min-w-0 flex-1 sm:w-64 sm:flex-none">
            <label for="f-error_code" class="sr-only">Код ошибки</label>
            <input id="f-error_code" name="error_code" type="text" value="{{ old('error_code', $question?->error_code) }}" placeholder="Код ошибки" class="{{ $pill }} w-36">
        </div>
        @foreach (['appliance_id', 'model', 'error_code'] as $name)
            @error($name)<p class="mx-4 mb-0 mt-2 text-[13px] text-red-600 sm:mx-5">{{ $message }}</p>@enderror
        @endforeach

        <div class="flex items-start gap-3 px-4 sm:px-5" :class="open ? 'pt-4' : 'cursor-pointer items-center py-3'" @click="if (!open) { open = true; $nextTick(() => $refs.title.focus()) }">
            <span x-show="!open" class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-400 text-black"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9a2.8 2.8 0 00-4-4L4 16v4zM13.5 6.5l4 4" /></svg></span>
            <label for="f-title" class="sr-only">Что случилось?</label>
            <textarea id="f-title" x-ref="title" @focus="open = true" name="title" rows="1" required
                :placeholder="open ? 'Что случилось? Например: Beko не сливает воду, ошибка E03' : 'Задать вопрос'"
                x-effect="open; $nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                @keydown.enter.prevent @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                :class="open ? 'text-xl font-bold leading-8 text-black sm:text-2xl sm:leading-9' : 'cursor-pointer text-[15px] leading-6 text-ink'"
                @error('title') aria-invalid="true" @enderror
                class="block w-full min-w-0 resize-none border-0 bg-transparent p-0 placeholder:font-normal placeholder:text-subtle focus:outline-none focus:ring-0">{{ old('title', $question?->title) }}</textarea>
        </div>
        @error('title')<p class="mx-4 mb-0 mt-1 text-[13px] text-red-600 sm:mx-5">{{ $message }}</p>@enderror

        <div x-show="open" x-cloak>
            <div class="px-4 pt-2 sm:px-5">
                <label for="f-description" class="sr-only">Подробности</label>
                <textarea id="f-description" name="description" rows="1" placeholder="Когда началось, что уже проверяли и меняли. Фото платы или дисплея (скрепка) помогут ответить точнее"
                    x-effect="open && $nextTick(() => grow($el))" @input="grow($el)" @paste="attach([...$event.clipboardData.files])"
                    @error('description') aria-invalid="true" @enderror
                    class="block max-h-[30rem] min-h-8 w-full resize-none border-0 bg-transparent p-0 text-[15px] leading-[22px] text-ink outline-none placeholder:text-subtle focus:ring-0">{{ old('description', $plain) }}</textarea>
                @error('description')<p class="mb-0 mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
            </div>

            <div x-show="photos.length" x-cloak class="flex flex-wrap gap-2 px-4 pt-3 sm:px-5">
                <template x-for="(p, i) in photos" :key="p.id">
                    <div class="relative size-20 overflow-hidden rounded-xl bg-surface">
                        <img :src="p.url" alt="" class="size-full object-cover">
                        <input type="hidden" name="images[]" :value="p.url">
                        <button type="button" @click="drop(i)" class="absolute right-1 top-1 grid size-5 place-items-center rounded-full bg-black/60 text-sm leading-none text-white" aria-label="Убрать фото">×</button>
                    </div>
                </template>
            </div>
            <p x-show="error" x-cloak x-text="error" class="mb-0 mt-2 px-4 text-sm text-red-600 sm:px-5"></p>
            @error('images.*')<p class="mb-0 mt-1 px-4 text-[13px] text-red-600 sm:px-5">{{ $message }}</p>@enderror

            <div class="mt-2 flex items-center justify-between border-t border-line px-2 py-1">
                <input x-ref="file" type="file" accept="image/*" multiple class="hidden" @change="attach($event.target.files)">
                <button type="button" @click="$refs.file.click()" :disabled="uploading || photos.length >= 10" aria-label="Прикрепить фото"
                    class="grid size-11 place-items-center rounded-full text-subtle disabled:opacity-40">
                    <svg class="size-6" :class="uploading && 'animate-pulse'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5l-8.6 8.6a5.5 5.5 0 01-7.8-7.8l8.9-8.9a3.7 3.7 0 015.2 5.2l-8.9 8.9a1.8 1.8 0 01-2.6-2.6l8.2-8.2" /></svg>
                </button>
                <button type="submit" :disabled="sending || uploading" aria-label="{{ $question ? 'Сохранить изменения' : 'Опубликовать вопрос' }}" title="{{ $question ? 'Сохранить изменения' : 'Опубликовать вопрос' }}"
                    class="grid size-11 place-items-center rounded-full text-black disabled:opacity-40">
                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 20.4l17.5-7.5a1 1 0 000-1.8L3.4 3.6a.9.9 0 00-1.3 1.1L4 10.5l9 1.5-9 1.5-1.9 5.8a.9.9 0 001.3 1.1z" /></svg>
                </button>
            </div>
        </div>
    </div>
</form>
