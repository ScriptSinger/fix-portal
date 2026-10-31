@if ($latest_discussions->isNotEmpty())
    <x-ui.widget title="Последние обсуждения" title-class="sr-only" aria-label="Последние комментарии и ответы" class="overflow-hidden bg-surface p-0">
        <div class="flex items-center gap-2 border-b border-line bg-white px-4 py-3">
            <div class="min-w-0">
                <h2 class="m-0 text-[16px] font-bold leading-5 text-ink">Последние обсуждения</h2>
            </div>
        </div>
        <ul class="space-y-2 p-3">
            @foreach ($latest_discussions as $item)
                @php($message = $item['message'])
                <li class="{{ $item['firstInSeries'] ? 'mt-2' : '-mt-1' }}">
                        <div class="group flex min-w-0 items-end gap-2">
                        @if ($item['firstInSeries'])
                            <x-ui.avatar :user="$message->user" size="size-8" profile />
                        @else
                            <span class="size-8 shrink-0" aria-hidden="true"></span>
                        @endif
                        <a href="{{ $item['url'] }}" aria-label="{{ $item['kind'] }}: {{ $item['subject']->title }} — {{ $item['excerpt'] }}" class="min-w-0 flex-1 rounded-lg no-underline focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-1">
                        <span class="relative block rounded-2xl rounded-bl-md bg-white px-3 py-2 shadow-[0_1px_1px_rgba(0,0,0,.08)] ring-1 ring-line transition group-hover:bg-brand-50">
                            @if ($item['firstInSeries'])<span class="telegram-widget-tail" aria-hidden="true"></span>@endif
                            <span class="flex items-center justify-between gap-2 text-xs leading-4">
                                @if ($item['firstInSeries'])
                                    <span class="truncate font-bold text-ink group-hover:text-black">{{ $message->user->name }}</span>
                                @else
                                    <span class="truncate text-[11px] text-subtle">{{ $item['kind'] }}</span>
                                @endif
                                <time class="shrink-0 text-[10px] text-subtle" datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->inDisplayTimezone()->format('H:i') }}</time>
                            </span>
                            <span class="mt-0.5 block truncate text-[11px] leading-4 text-subtle">{{ $item['subject']->title }}</span>
                            <span class="mt-1 block line-clamp-2 text-[13px] leading-[18px] text-body group-hover:text-ink">{{ $item['excerpt'] }}</span>
                        </span>
                        </a>
                        </div>
                </li>
            @endforeach
        </ul>
    </x-ui.widget>
@endif
