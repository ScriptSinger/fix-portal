@if ($latest_discussions->isNotEmpty())
    <x-ui.widget title="Последние обсуждения" title-class="sr-only" aria-label="Последние комментарии и ответы" class="overflow-hidden bg-[#e8f3fb] p-0">
        <div class="flex items-center gap-2 border-b border-[#cfe3ef] bg-white/75 px-4 py-3">
            <div class="min-w-0">
                <h2 class="m-0 text-[16px] font-bold leading-5 text-ink">Последние обсуждения</h2>
            </div>
        </div>
        <ul class="space-y-2 p-3">
            @foreach ($latest_discussions as $item)
                @php($message = $item['message'])
                <li>
                    <a href="{{ $item['url'] }}" class="group flex min-w-0 items-end gap-2 no-underline">
                        <x-ui.avatar :user="$message->user" size="size-8" />
                        <span class="relative min-w-0 flex-1 rounded-2xl rounded-bl-md bg-white px-3 py-2 shadow-[0_1px_1px_rgba(0,0,0,.08)] ring-1 ring-black/5 transition group-hover:bg-[#f8fcff]">
                            <span class="telegram-widget-tail" aria-hidden="true"></span>
                            <span class="flex items-center justify-between gap-2 text-xs leading-4">
                                <span class="truncate font-bold text-[#168acb] group-hover:text-[#087dbb]">{{ $message->user->name }}</span>
                                <time class="shrink-0 text-[10px] text-subtle" datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->inDisplayTimezone()->format('H:i') }}</time>
                            </span>
                            <span class="mt-0.5 block truncate text-[11px] leading-4 text-subtle">{{ $item['subject']->title }}</span>
                            <span class="mt-1 block line-clamp-2 text-[13px] leading-[18px] text-body group-hover:text-ink">{{ $item['excerpt'] }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-ui.widget>
@endif
