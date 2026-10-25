@props(['active' => null])
{{-- Техника, по которой задают вопросы (/appliances/...) --}}
<x-ui.side-nav heading="Техника" label="Вид техники" :items="collect([['title' => 'Все вопросы', 'url' => route('questions.index'), 'active' => $active === null]])
    ->concat($popular_appliances->map(fn ($a) => ['title' => $a->title, 'url' => route('public.appliances.show', ['appliance' => $a->slug]), 'count' => $a->questions_count, 'active' => $active === $a->slug]))"  />
