@props(['active' => null])
{{-- Техника, по которой задают вопросы (/appliances/...) --}}
<x-ui.side-nav heading="Техника" label="Вид техники" :items="collect([['title' => 'Все вопросы', 'url' => route('questions.index'), 'active' => $active === null]])
    ->concat($popular_appliances->map(fn ($a) => ['title' => $a->title, 'url' => route('public.appliances.show', ['appliance' => $a->slug]), 'count' => $a->questions_count, 'active' => $active === $a->slug]))" >
    <div class="rounded-[5px] bg-brand-400 p-4">
        <p class="mb-3 text-sm leading-[22px] text-black"><b>Не нашли ответ?</b> Опишите проблему: ответят мастера и участники сообщества.</p>
        <x-ui.button variant="dark" :href="route('questions.create')" class="w-full">Задать вопрос</x-ui.button>
    </div>
</x-ui.side-nav>
