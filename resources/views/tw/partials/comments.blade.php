@php
    $count = $instance->comments->count();
    $field = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-400/40';
    $trash = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3" />';
@endphp
<section id="comments" class="mt-12" aria-labelledby="comments-title">
    <h2 id="comments-title" class="text-2xl font-bold">
        {{ $count }} {{ trans_choice('комментарий|комментария|комментариев', $count, [], 'ru') }}
    </h2>

    <ul class="mt-6 space-y-4">
        @foreach ($instance->comments as $comment)
            <li x-data="{ reply: false, replies: true }">
                <x-tw.card>
                    <div class="flex gap-3">
                        <x-tw.avatar :user="$comment->user" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-3 text-sm">
                                <span class="font-bold">{{ $comment->user->name }}</span>
                                <span class="text-muted">{{ $comment->dateAsCarbon->diffForHumans() }}</span>
                                @can('delete', $comment)
                                    <form action="{{ route('comments.destroy', ['id' => $comment->id]) }}" method="POST"
                                        @submit="if (!confirm('Подтвердите удаление')) $event.preventDefault()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-muted hover:text-red-600" aria-label="Удалить">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.8" aria-hidden="true">{!! $trash !!}</svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                            <div class="prose mt-2 max-w-none whitespace-pre-line">{!! $comment->text !!}</div>

                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                <x-tw.likes :instance="$comment" />
                                @auth('web')
                                    <button type="button" @click="reply = !reply"
                                        class="font-medium text-muted hover:text-ink">Ответить</button>
                                @endauth
                                @if ($comment->replies->isNotEmpty())
                                    <button type="button" @click="replies = !replies"
                                        class="font-medium text-muted hover:text-ink"
                                        x-text="replies ? 'Скрыть ответы ({{ $comment->replies->count() }})' : 'Показать ответы ({{ $comment->replies->count() }})'"></button>
                                @endif
                            </div>

                            @auth('web')
                                <form x-show="reply" x-collapse x-cloak method="POST"
                                    action="{{ route('comments.replies.store', ['id' => $comment->id]) }}"
                                    class="mt-3 space-y-3">
                                    @csrf
                                    <label for="reply-{{ $comment->id }}" class="sr-only">Ваш ответ</label>
                                    <textarea id="reply-{{ $comment->id }}" name="text" rows="3" required placeholder="Ваш ответ"
                                        class="{{ $field }}"></textarea>
                                    <x-tw.button variant="dark" size="sm">Отправить</x-tw.button>
                                </form>
                            @endauth
                        </div>
                    </div>

                    @if ($comment->replies->isNotEmpty())
                        <ul x-show="replies" x-collapse class="mt-4 space-y-4 border-l-2 border-brand-400 pl-4 sm:ml-12">
                            @foreach ($comment->replies as $reply)
                                <li class="flex gap-3">
                                    <x-tw.avatar :user="$reply->user" size="size-8" />
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-3 text-sm">
                                            <span class="font-bold">{{ $reply->user->name }}</span>
                                            <span class="text-muted">{{ $reply->dateAsCarbon->diffForHumans() }}</span>
                                            @can('delete', $reply)
                                                <form action="{{ route('comments.replies.destroy', ['id' => $reply->id]) }}"
                                                    method="POST"
                                                    @submit="if (!confirm('Подтвердите удаление')) $event.preventDefault()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-muted hover:text-red-600"
                                                        aria-label="Удалить">
                                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor" stroke-width="1.8"
                                                            aria-hidden="true">{!! $trash !!}</svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                        <div class="prose mt-1 max-w-none whitespace-pre-line">{!! $reply->text !!}</div>
                                        <div class="mt-2"><x-tw.likes :instance="$reply" /></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-tw.card>
            </li>
        @endforeach
    </ul>

    <div class="mt-8">
        @auth('web')
            <x-tw.card>
                <h3 class="text-lg font-bold">Оставить комментарий</h3>
                <form method="POST" class="mt-3 space-y-3"
                    action="{{ route('comments.store', ['type' => strtolower(class_basename($instance)), 'id' => $instance->id]) }}">
                    @csrf
                    <label for="comment-text" class="sr-only">Комментарий</label>
                    <textarea id="comment-text" name="text" rows="5" required class="{{ $field }}"></textarea>
                    @error('text')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <x-tw.button variant="dark">Отправить</x-tw.button>
                </form>
            </x-tw.card>
        @endauth
        @guest
            <p class="rounded-xl border border-line bg-white p-5 text-muted">
                Чтобы оставить комментарий, необходимо
                <a class="font-medium text-link hover:underline" href="{{ route('register') }}">зарегистрироваться</a>
                или <a class="font-medium text-link hover:underline" href="{{ route('login') }}">войти</a>.
            </p>
        @endguest
    </div>
</section>
