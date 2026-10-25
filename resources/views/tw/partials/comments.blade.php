@php
    $count = $instance->comments->count();
    $field = 'field';
    $box = 'relative border border-dashed border-[#dadada] px-8 py-12 max-sm:px-4';
    $trash = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3" />';
    $when = 'ml-3 text-[11px] uppercase tracking-[2px] text-[#999]';
@endphp
<section id="comments" class="mt-12" aria-label="Комментарии">
    <div class="{{ $box }}">
        <x-tw.legend dark>{{ $count }} {{ trans_choice('комментарий|комментария|комментариев', $count, [], 'ru') }}</x-tw.legend>

        <ul class="space-y-8">
            @foreach ($instance->comments as $comment)
                <li x-data="{ reply: false, replies: true }">
                    <div class="flex items-center gap-4">
                        <x-tw.avatar :user="$comment->user" />
                        <div class="text-[15px]">
                            <span class="text-body">{{ $comment->user->name }}</span>
                            <span class="{{ $when }}">{{ $comment->dateAsCarbon->diffForHumans() }}</span>
                            @can('delete', $comment)
                                <form action="{{ route('comments.destroy', ['id' => $comment->id]) }}" method="POST" class="ml-3 inline"
                                    @submit="if (!confirm('Подтвердите удаление')) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[#999] hover:text-red-600" aria-label="Удалить">
                                        <svg class="inline size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $trash !!}</svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="article-content mt-6 whitespace-pre-line">{!! $comment->text !!}</div>

                    <div class="mt-6 flex flex-wrap items-center gap-4 text-xs">
                        <x-tw.likes :instance="$comment" />
                        @auth('web')
                            <button type="button" @click="reply = !reply" class="font-bold text-[#999] hover:text-ink">Ответить</button>
                        @endauth
                    </div>

                    @auth('web')
                        <form x-show="reply" x-collapse x-cloak method="POST"
                            action="{{ route('comments.replies.store', ['id' => $comment->id]) }}" class="mt-4 space-y-3">
                            @csrf
                            <label for="reply-{{ $comment->id }}" class="sr-only">Ваш ответ</label>
                            <textarea id="reply-{{ $comment->id }}" name="text" rows="3" required placeholder="Ваш ответ" class="{{ $field }}"></textarea>
                            <x-tw.button variant="dark" size="sm">Отправить</x-tw.button>
                        </form>
                    @endauth

                    <div class="{{ $box }} mt-10 !px-6 !py-8">
                        <x-tw.legend>
                            <button type="button" @click="replies = !replies">{{ $comment->replies->count() }} {{ trans_choice('ответ|ответа|ответов', $comment->replies->count(), [], 'ru') }}</button>
                        </x-tw.legend>
                        <ul x-show="replies" x-collapse class="space-y-6">
                            @foreach ($comment->replies as $reply)
                                <li>
                                    <div class="flex items-center gap-3">
                                        <x-tw.avatar :user="$reply->user" size="size-10" />
                                        <div class="text-[15px]">
                                            <span class="text-body">{{ $reply->user->name }}</span>
                                            <span class="{{ $when }}">{{ $reply->dateAsCarbon->diffForHumans() }}</span>
                                            @can('delete', $reply)
                                                <form action="{{ route('comments.replies.destroy', ['id' => $reply->id]) }}" method="POST" class="ml-3 inline"
                                                    @submit="if (!confirm('Подтвердите удаление')) $event.preventDefault()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-[#999] hover:text-red-600" aria-label="Удалить">
                                                        <svg class="inline size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $trash !!}</svg>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                    <div class="article-content mt-3 whitespace-pre-line">{!! $reply->text !!}</div>
                                    <div class="mt-3"><x-tw.likes :instance="$reply" /></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mt-12">
        @auth('web')
            <div class="{{ $box }}">
                <x-tw.legend>Оставить комментарий</x-tw.legend>
                <form method="POST" class="space-y-3"
                    action="{{ route('comments.store', ['type' => strtolower(class_basename($instance)), 'id' => $instance->id]) }}">
                    @csrf
                    <label for="comment-text" class="sr-only">Комментарий</label>
                    <textarea id="comment-text" name="text" rows="5" required class="{{ $field }}"></textarea>
                    @error('text')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <x-tw.button variant="dark">Отправить</x-tw.button>
                </form>
            </div>
        @endauth
        @guest
            <p>Чтобы оставить комментарий, необходимо
                <a class="text-[#0275d8] hover:underline" href="{{ route('register') }}">зарегистрироваться</a>
                или <a class="text-[#0275d8] hover:underline" href="{{ route('login') }}">войти</a></p>
        @endguest
    </div>
</section>
