@props(['title', 'text' => null, 'action' => null, 'url' => null, 'secondary' => null, 'secondaryUrl' => null])
{{-- Пустой список: что произошло, почему и что делать дальше (ссылкой на действие) --}}
<div {{ $attributes->class('rounded-card bg-white px-6 py-10 text-center shadow-sm') }}>
    <p class="m-0 text-lg font-bold text-black">{{ $title }}</p>
    @if ($text)<p class="mx-auto mb-0 mt-2 max-w-md text-[15px] leading-6">{{ $text }}</p>@endif
    @if ($action || $secondary)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
            @if ($action)<x-ui.button variant="dark" size="sm" :href="$url">{{ $action }}</x-ui.button>@endif
            @if ($secondary)<a href="{{ $secondaryUrl }}" class="keep text-sm font-bold text-black underline underline-offset-4">{{ $secondary }}</a>@endif
        </div>
    @endif
</div>
