@props(['instance'])
@php
    $type = strtolower(class_basename($instance));
    $likes = isset($instance->likes) ? count($instance->likes) : 0;
    $dislikes = isset($instance->dislikes) ? count($instance->dislikes) : 0;
    $pill = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-sm text-muted';
    $up = '<path stroke-linecap="round" stroke-linejoin="round" d="M7 11v9H4v-9h3zm0 0l4-7a2 2 0 012 2v3h5.5a1.5 1.5 0 011.46 1.84l-1.5 6A2 2 0 0116 20H7" />';
    $down = '<path stroke-linecap="round" stroke-linejoin="round" d="M7 13V4H4v9h3zm0 0l4 7a2 2 0 002-2v-3h5.5a1.5 1.5 0 001.46-1.84l-1.5-6A2 2 0 0016 4H7" />';
@endphp
@auth
    <div x-data="likeBox({ like: {{ $likes }}, dislike: {{ $dislikes }},
        likeUrl: @js(route('like', ['type' => $type, 'id' => $instance->id])),
        dislikeUrl: @js(route('dislike', ['type' => $type, 'id' => $instance->id])) })"
        class="inline-flex items-center gap-1">
        <button type="button" @click="send('like')" :disabled="busy" aria-label="Нравится"
            class="{{ $pill }} hover:bg-brand-50 hover:text-ink disabled:opacity-60">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">{!! $up !!}</svg>
            <span x-text="like">{{ $likes }}</span>
        </button>
        <button type="button" @click="send('dislike')" :disabled="busy" aria-label="Не нравится"
            class="{{ $pill }} hover:bg-brand-50 hover:text-ink disabled:opacity-60">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                aria-hidden="true">{!! $down !!}</svg>
            <span x-text="dislike">{{ $dislikes }}</span>
        </button>
    </div>
@else
    <div class="inline-flex items-center gap-1">
        <span class="{{ $pill }}"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.8" aria-hidden="true">{!! $up !!}</svg>{{ $likes }}</span>
        <span class="{{ $pill }}"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="1.8" aria-hidden="true">{!! $down !!}</svg>{{ $dislikes }}</span>
    </div>
@endauth
