@if ($latest_discussions->isNotEmpty())
    <x-ui.widget title="Последние обсуждения" title-class="sr-only" aria-label="Последние комментарии и ответы" class="overflow-hidden bg-surface p-0">
        <div class="flex items-center gap-2 border-b border-line bg-white px-4 py-2.5">
            <div class="min-w-0">
                <h2 class="m-0 text-[15px] font-bold leading-5 text-ink">Последние обсуждения</h2>
            </div>
        </div>
        <ul class="discussion-pattern space-y-1.5 p-3" style="background-image: linear-gradient(rgba(241, 241, 241, .7), rgba(241, 241, 241, .7)), url('{{ asset('assets/front/images/discussions-pattern.webp') }}')">
            @foreach ($latest_discussions as $item)
                @php($message = $item['message'])
                <li class="{{ $item['firstInSeries'] ? 'mt-1' : '-mt-0.5' }} {{ $loop->iteration > 3 ? 'max-lg:hidden' : '' }}">
                        <div class="group flex min-w-0 items-end gap-2">
                        @if ($item['lastInSeries'])
                            <x-ui.avatar :user="$message->user" size="size-8" profile />
                        @else
                            <span class="size-8 shrink-0" aria-hidden="true"></span>
                        @endif
                        <a href="{{ $item['url'] }}" aria-label="{{ $item['kind'] }}: {{ $item['subject']->title }} — {{ $item['excerpt'] }}" class="min-w-0 flex-1 rounded-lg no-underline focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-1">
                        <span class="relative block rounded-2xl rounded-bl-md bg-white px-3 py-1.5 shadow-[0_1px_1px_rgba(0,0,0,.08)] ring-1 ring-line transition group-hover:bg-brand-50">
                            <span class="flex items-center justify-between gap-2 text-xs leading-4">
                                @if ($item['firstInSeries'])
                                    <span class="truncate font-bold text-ink group-hover:text-black">{{ $message->user->name }}</span>
                                @else
                                    <span class="truncate text-[11px] text-subtle">{{ $item['kind'] }}</span>
                                @endif
                                <time class="shrink-0 text-[10px] text-subtle" datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->inDisplayTimezone()->format('H:i') }}</time>
                            </span>
                            <span class="mt-0.5 block truncate text-[11px] font-bold leading-4 text-ink">{{ $item['subject']->title }}</span>
                            <span class="mt-0.5 block max-h-[34px] overflow-hidden text-[12px] leading-[17px] text-body [display:-webkit-box] [-webkit-box-orient:vertical] [-webkit-line-clamp:2] group-hover:text-ink">{{ $item['excerpt'] }}</span>
                        </span>
                        </a>
                        </div>
                </li>
            @endforeach
        </ul>
    </x-ui.widget>
@endif
