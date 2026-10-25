@props(['instance'])
{{-- Реакции под сообщением, как в Telegram: чипы с эмодзи и числом, своя подсвечена; кнопка со смайликом открывает панель выбора (только для вошедших) --}}
@php
    $type = strtolower(class_basename($instance));
    $counts = $instance->reactions->groupBy('emoji')->map->count();
    $mine = optional($instance->reactions->firstWhere('user_id', auth('web')->id()))->emoji;
@endphp
<div x-data="reactions({ url: @js(route('reactions.toggle', ['type' => $type, 'id' => $instance->id])), counts: @js($counts), mine: @js($mine), order: @js(\App\Models\Reaction::EMOJI) })"
    @click.outside="open = false" class="relative inline-flex flex-wrap items-center gap-1">
    <template x-for="chip in chips" :key="chip.emoji">
        @auth('web')
            <button type="button" @click="react(chip.emoji)" :disabled="busy" :aria-pressed="mine === chip.emoji"
                :class="mine === chip.emoji ? 'bg-black text-white' : 'bg-black/5 text-ink'"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[13px] leading-5"><span class="emoji text-base" x-text="chip.emoji"></span><span class="font-medium" x-text="chip.n"></span></button>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-black/5 px-2 py-0.5 text-[13px] leading-5 text-ink"><span class="emoji text-base" x-text="chip.emoji"></span><span class="font-medium" x-text="chip.n"></span></span>
        @endauth
    </template>
    @auth('web')
        <button type="button" @click="open = !open" aria-label="Добавить реакцию" :aria-expanded="open"
            class="grid size-6 place-items-center rounded-full text-subtle hover:bg-black/5 hover:text-ink">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M8.5 14.5a4.5 4.5 0 007 0M9 9.5h.01M15 9.5h.01" /></svg>
        </button>
        <div x-show="open" x-cloak x-transition.opacity class="absolute bottom-full left-0 z-30 mb-1 flex gap-0.5 rounded-full bg-white p-1 shadow-lg ring-1 ring-black/10">
            <template x-for="e in order" :key="e">
                <button type="button" @click="react(e)" class="emoji grid size-10 place-items-center rounded-full text-2xl hover:bg-black/5" x-text="e"></button>
            </template>
        </div>
    @endauth
</div>
