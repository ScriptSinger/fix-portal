{{-- Общая форма вопроса (создание и правка). Подсказки направляют к заголовку с симптомом: по нему вопрос находят в поиске --}}
@props(['action', 'question' => null, 'appliances', 'method' => null])
<form method="POST" action="{{ $action }}" x-data="{ busy: false }" @submit="busy = true">
    @csrf
    @if ($method) @method($method) @endif

    <x-ui.field name="title" label="Что случилось?" :value="$question?->title" large required
        placeholder="Например: стиральная машина Beko не сливает воду, ошибка E03"
        hint="Опишите симптом и назовите прибор. По такому заголовку ваш вопрос найдут в поиске и быстрее ответят." />

    <div class="grid gap-x-6 sm:grid-cols-2">
        <x-ui.select name="appliance_id" label="Тип прибора" :options="$appliances" :value="$question?->appliance_id" />
        <x-ui.field name="model" label="Марка и модель" :value="$question?->model" placeholder="Beko WKL 13540 K"
            hint="Модель указана на наклейке прибора или платы." />
    </div>

    <x-ui.field name="error_code" label="Код ошибки (если есть)" :value="$question?->error_code" placeholder="E03" class="field sm:max-w-[240px]" />

    <div class="mb-6">
        <label class="mb-2 block">Подробности</label>
        <p class="mb-2 mt-0 text-[13px] text-subtle">Когда началось, что уже проверяли и меняли. Приложите фото платы, дисплея или места поломки: так ответят точнее.</p>
        <x-ui.editor name="description" :value="$question?->description ?? ''"
            :upload-url="route('api.users.images.upload')" :destroy-url="route('api.users.images.destroy', ['image' => ':id'])" />
        @error('description')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
    </div>

    <x-ui.button variant="dark" size="lg" x-bind:disabled="busy"><span x-text="busy ? 'Отправляем…' : '{{ $question ? 'Сохранить изменения' : 'Опубликовать вопрос' }}'">{{ $question ? 'Сохранить изменения' : 'Опубликовать вопрос' }}</span></x-ui.button>
</form>
