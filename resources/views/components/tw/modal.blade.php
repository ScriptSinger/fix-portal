@props(['title', 'id'])
<div x-show="{{ $id }}" x-cloak x-transition.opacity @keydown.escape.window="{{ $id }} = false"
    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <div @click.outside="{{ $id }} = false" class="max-h-full w-full max-w-[500px] overflow-y-auto rounded border border-black/20 bg-white text-body">
        <div class="flex items-start justify-between gap-4 border-b border-[#e9ecef] p-4">
            <h5 class="text-[20px] font-bold leading-6 text-black">{{ $title }}</h5>
            <button type="button" @click="{{ $id }} = false" class="text-2xl leading-none text-black/50 hover:text-black" aria-label="Закрыть">&times;</button>
        </div>
        <div class="p-4">{{ $slot }}</div>
        <div class="flex justify-end border-t border-[#e9ecef] p-4">
            <button type="button" @click="{{ $id }} = false" class="rounded bg-[#6c757d] px-3 py-2 text-white hover:bg-[#5a6268]">Закрыть</button>
        </div>
    </div>
</div>
