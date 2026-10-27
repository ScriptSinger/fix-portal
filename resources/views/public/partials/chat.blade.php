@php
    // Комментарии и ответы одной лентой по времени; ответ показывает цитату своего комментария
    $items = collect();
    foreach ($instance->comments as $comment) {
        $items->push(['kind' => 'comment', 'model' => $comment, 'root' => $comment->id, 'quote' => null, 'at' => $comment->created_at]);
        foreach ($comment->replies as $reply) {
            // цитата: конкретный ответ, если отвечали на него, иначе сам комментарий
            $quoted = $reply->quote_reply_id ? $comment->replies->firstWhere('id', $reply->quote_reply_id) : null;
            $items->push(['kind' => 'reply', 'model' => $reply, 'root' => $comment->id, 'quote' => $quoted ?? $comment, 'at' => $reply->created_at]);
        }
    }
    $items = $items->sortBy('at')->values();
    $total = $items->count();
    $people = $items->pluck('model.user_id')->unique()->count();

    $names = ['text-orange-700', 'text-teal-700', 'text-violet-700', 'text-pink-700', 'text-blue-700'];
    $bars = ['border-orange-700', 'border-teal-700', 'border-violet-700', 'border-pink-700', 'border-blue-700'];
    $fills = ['bg-orange-700', 'bg-teal-700', 'bg-violet-700', 'bg-pink-700', 'bg-blue-700'];
    $plain = fn (?string $html, int $n = 90) => \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), $n);
    $trash = '<path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3" />';
    $authorId = $instance->user_id ?? null;
    $storeUrl = route('comments.store', ['type' => strtolower(class_basename($instance)), 'id' => $instance->id]);
    $day = null;
    $isQuestion = $instance instanceof \App\Models\Question;
    // одна лента для вопросов, статей и прошивок; слова только разные: у вопроса «ответы», у остальных «комментарии»
    $words = $isQuestion ? ['Ответы', 'Пока нет ответов, напишите первым', 'Написать ответ', 'Чтобы ответить'] : ['Комментарии', 'Пока нет комментариев, напишите первым', 'Написать комментарий', 'Чтобы написать комментарий'];
    $solution = $isQuestion ? $instance->solution() : null;
    $meId = auth('web')->id();
    $solved = \App\Services\Reputation::counts($items->pluck('model.user_id')->unique()->values()->all());
    $iAmAuthor = $isQuestion && $meId && $instance->user_id === $meId;
@endphp
<section id="comments" aria-label="{{ $words[0] }}" class="mt-4" @scroll.window.passive="measure()" @resize.window="measure()" x-init="measure()" x-data="{
    target: null, base: @js($storeUrl), photos: [], busy: false, error: '', far: false,
    measure() { this.far = this.$root.getBoundingClientRect().bottom - window.innerHeight > 240; },
    // едем к концу ленты: поле ввода закреплено и всегда «в кадре», так что scrollIntoView к нему не работал, а $el в обработчике кнопки это сама кнопка, поэтому берём $root
    toEnd() { window.scrollBy({ top: this.$root.getBoundingClientRect().bottom - window.innerHeight + 24, behavior: 'smooth' }); },
    async attach(files) {
        this.error = '';
        for (const file of [...files].filter((f) => f.type.startsWith('image/')).slice(0, 6 - this.photos.length)) {
            this.busy = true;
            try {
                const body = new FormData(); body.append('file', file);
                const r = await fetch(@js(route('api.users.images.upload')), { method: 'POST', body, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '', Accept: 'application/json' }, credentials: 'same-origin' });
                const d = await r.json();
                if (!r.ok) { this.error = Object.values(d.errors ?? {}).flat().join(' ') || 'Не удалось загрузить фото'; continue; }
                this.photos.push({ id: d.id, url: d.url });
            } catch (e) { this.error = 'Не удалось загрузить фото'; }
        }
        this.busy = false; this.$refs.file.value = '';
    },
    drop(i) {
        const [p] = this.photos.splice(i, 1);
        fetch(@js(route('api.users.images.destroy', ['image' => ':id'])).replace(':id', p.id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '', Accept: 'application/json' }, credentials: 'same-origin' });
    },
    grow(el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 160) + 'px'; },
    submit(e) { if (this.busy || (!this.$refs.text.value.trim() && !this.photos.length)) e.preventDefault(); }
}">
    <div class="flex justify-center">
        <h2 class="m-0 rounded-full bg-black/5 px-3 py-0.5 text-xs font-medium leading-5 text-subtle">
            @if ($total)
                {{ $words[0] }} · {{ $total }} {{ trans_choice('сообщение|сообщения|сообщений', $total, [], 'ru') }} · {{ $people }} {{ trans_choice('участник|участника|участников', $people, [], 'ru') }}
            @else
                {{ $words[1] }}
            @endif
        </h2>
    </div>

    @if ($solution)
        @php $sid = ($instance->solution_type === 'comment' ? 'c-' : 'r-') . $solution->id; @endphp
        <a href="#{{ $sid }}" class="keep mt-4 block rounded-2xl bg-green-50 px-4 py-3 text-ink no-underline ring-1 ring-green-600/30">
            <b class="text-sm text-green-700">✓ Решение · {{ $solution->user->name }}</b>
            <span class="mt-0.5 block text-[14px] leading-snug text-ink/80"><span class="line-clamp-2">{{ $plain($solution->text, 200) ?: 'Фото' }}</span></span>
        </a>
    @endif

    <div class="grid gap-[3px] py-5">
        @foreach ($items as $i => $item)
            @php
                $m = $item['model'];
                [$body, $pics] = \App\Support\ContentImages::extract($m->text);
                $prev = $items[$i - 1]['model'] ?? null;
                $next = $items[$i + 1]['model'] ?? null;
                $first = ! $prev || $prev->user_id !== $m->user_id || $m->created_at->toDateString() !== $prev->created_at->toDateString();
                $last = ! $next || $next->user_id !== $m->user_id || $m->created_at->toDateString() !== $next->created_at->toDateString();
                $slot = $m->user_id % 5;
                $own = $authorId && $m->user_id === $authorId;
                $id = ($item['kind'] === 'comment' ? 'c-' : 'r-') . $m->id;
                $q = $item['quote'];
                $qslot = $q ? $q->user_id % 5 : 0;
                $qid = $q ? ($q instanceof \App\Models\Reply ? 'r-' : 'c-') . $q->id : null;
                $date = $m->created_at->toDateString();
                $type = $item['kind'] === 'comment' ? 'comment' : 'reply';
                $isSolution = $isQuestion && $instance->solution_type === $type && (int) $instance->solution_id === $m->id;
                $canSolve = $iAmAuthor && ! $isSolution && $m->user_id !== $meId;
            @endphp
            @if ($date !== $day)
                @php $day = $date; @endphp
                <div class="my-2 justify-self-center rounded-full bg-black/5 px-3 py-0.5 text-xs font-medium text-subtle">{{ $m->created_at->translatedFormat('j F Y') }}</div>
            @endif
            <div class="grid grid-cols-[2.25rem_minmax(0,1fr)] items-end gap-2 {{ $first ? 'mt-2.5' : '' }}">
                @if ($last)
                    <x-ui.avatar :user="$m->user" size="size-9" />
                @else
                    <span class="size-9" aria-hidden="true"></span>
                @endif
                <div id="{{ $id }}" class="relative {{ $pics ? 'w-[min(100%,28rem)]' : 'max-w-[min(100%,34rem)]' }} scroll-mt-24 justify-self-start rounded-2xl bg-white px-3 py-1.5 shadow-sm {{ $last ? 'rounded-bl-sm' : '' }} {{ $isSolution ? 'ring-2 ring-green-600' : '' }}">
                    @if ($first)
                        <div class="flex flex-wrap items-baseline gap-x-2 text-sm font-bold leading-snug {{ $names[$slot] }}">
                            {{ $m->user->name }}
                            @if (($n = $solved[$m->user_id] ?? 0) > 0)
                                <span class="text-[11px] font-bold text-green-700" title="{{ \App\Services\Reputation::rank($n) }}: {{ $n }} {{ trans_choice('решение|решения|решений', $n, [], 'ru') }}">✓ {{ $n }}</span>
                            @endif
                            @if ($own)
                                <span class="rounded bg-brand-400 px-1.5 text-[10px] font-bold uppercase tracking-wider text-black">автор</span>
                            @endif
                        </div>
                    @endif

                    @if ($q)
                        <a href="#{{ $qid }}" class="keep my-1 block rounded-r-md border-l-[3px] bg-black/5 px-2 py-0.5 text-[13px] leading-snug no-underline {{ $bars[$qslot] }} {{ $names[$qslot] }}">
                            <b class="block">{{ $q->user->name }}</b>
                            <span class="block truncate text-ink/70">{{ $plain($q->text) }}</span>
                        </a>
                    @endif

                    @if ($pics)
                        <x-ui.photo-grid :photos="$pics" :alt="$item['kind'] === 'comment' ? 'Комментарий' : 'Ответ'" class="my-1 rounded-lg" />
                    @endif
                    @if ($body !== '')
                        <div class="article-content {{ $body === strip_tags($body) ? 'whitespace-pre-line' : '' }} text-[15px] leading-[1.45] [overflow-wrap:anywhere]">{!! \App\Support\ContentImages::withAlt($body, $item['kind'] === 'comment' ? 'Комментарий' : 'Ответ') !!}</div>
                    @endif

                    <div class="mt-1 flex flex-wrap items-end justify-between gap-x-3 gap-y-1">
                        <x-ui.reactions :instance="$m" />
                        <div class="ml-auto flex items-center gap-x-3 text-[11px] text-subtle">
                            @if ($isSolution)<span class="font-bold text-green-700">✓ Решение</span>@endif
                            @if ($canSolve)
                                <form method="POST" action="{{ route('questions.solution.store', ['id' => $instance->id]) }}" class="inline">@csrf
                                    <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="message" value="{{ $m->id }}">
                                    <button type="submit" class="font-medium hover:text-green-700">Это решение</button>
                                </form>
                            @endif
                            @auth('web')
                                <button type="button" class="font-medium hover:text-ink"
                                    @click="target = { root: {{ $item['root'] }}, reply: {{ $item['kind'] === 'reply' ? $m->id : 'null' }}, name: @js($m->user->name), text: @js($plain($m->text, 80)) }; $nextTick(() => $refs.text.focus())">Ответить</button>
                            @endauth
                            @can('delete', $m)
                                <form action="{{ route($item['kind'] === 'comment' ? 'comments.destroy' : 'comments.replies.destroy', ['id' => $m->id]) }}" method="POST" class="inline"
                                    @submit="if (!confirm('Подтвердите удаление')) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="hover:text-red-600" aria-label="Удалить">
                                        <svg class="inline size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $trash !!}</svg>
                                    </button>
                                </form>
                            @endcan
                            <time datetime="{{ $m->created_at->toIso8601String() }}" title="{{ $m->created_at->translatedFormat('j F Y, H:i') }}">{{ $m->created_at->format('H:i') }}</time>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="sticky bottom-0 z-20 bg-surface pb-3 pt-3">
        {{-- Стрелка вниз занимает нижнее место, «Наверх» поднимается над ней (store jump, см. layouts/app.blade.php) --}}
        <button type="button" x-show="far" x-cloak x-transition.opacity x-effect="$store.jump.show = far" @click="toEnd()" aria-label="К последним сообщениям"
            class="fixed {{ View::hasSection('has-composer') ? 'max-lg:bottom-20' : 'max-lg:bottom-4' }} right-4 z-30 flex size-12 items-center justify-center bg-brand-400 text-ink shadow-[0_2px_10px_rgba(0,0,0,.3)] lg:bottom-10 lg:right-6 lg:size-10 lg:shadow-none">
            <x-ui.icon name="chevron-down" class="size-5" />
        </button>
        @auth('web')
            <form method="POST" class="grid gap-2" @submit="submit($event)"
                :action="target ? @js(route('comments.replies.store', ['id' => '__ID__'])).replace('__ID__', target.root) : base">
                @csrf
                <input type="hidden" name="quote_reply_id" :value="target && target.reply" :disabled="!target || !target.reply">
                <div x-show="target" x-cloak class="flex items-center justify-between gap-2 border-l-[3px] border-teal-700 px-2 text-[13px] leading-snug text-teal-700">
                    <div class="min-w-0">
                        <b class="block" x-text="target && target.name"></b>
                        <span class="block truncate text-ink/70" x-text="target && target.text"></span>
                    </div>
                    <button type="button" class="text-xl leading-none text-subtle hover:text-ink" aria-label="Отменить ответ" @click="target = null">×</button>
                </div>
                <div x-show="photos.length" x-cloak class="flex flex-wrap gap-2">
                    <template x-for="(p, i) in photos" :key="p.id">
                        <div class="relative size-16 overflow-hidden rounded-lg bg-surface">
                            <img :src="p.url" alt="" class="size-full object-cover">
                            <input type="hidden" name="images[]" :value="p.url">
                            <button type="button" @click="drop(i)" class="absolute right-0.5 top-0.5 grid size-5 place-items-center rounded-full bg-black/60 text-sm leading-none text-white" aria-label="Убрать фото">×</button>
                        </div>
                    </template>
                </div>
                <div class="flex items-end gap-1 rounded-3xl bg-white px-1 ring-1 ring-black/10">
                    <input x-ref="file" type="file" accept="image/*" multiple class="hidden" @change="attach($event.target.files)">
                    <button type="button" @click="$refs.file.click()" :disabled="busy || photos.length >= 6" aria-label="Прикрепить фото"
                        class="grid size-11 shrink-0 place-items-center rounded-full text-subtle hover:text-ink disabled:opacity-40">
                        <svg class="size-6" :class="busy && 'animate-pulse'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5l-8.6 8.6a5.5 5.5 0 01-7.8-7.8l8.9-8.9a3.7 3.7 0 015.2 5.2l-8.9 8.9a1.8 1.8 0 01-2.6-2.6l8.2-8.2" /></svg>
                    </button>
                    <label for="chat-text" class="sr-only">Сообщение</label>
                    <textarea id="chat-text" x-ref="text" name="text" rows="1" placeholder="{{ $words[2] }}" @input="grow($el)"
                        @keydown.ctrl.enter.prevent="$el.form.requestSubmit()" @paste="attach([...$event.clipboardData.files])"
                        class="max-h-40 min-w-0 flex-1 resize-none border-0 bg-transparent px-1 py-[11px] text-[15px] leading-[22px] text-ink outline-none focus:ring-0"></textarea>
                    <button type="submit" aria-label="Отправить" :disabled="busy"
                        class="grid size-11 shrink-0 place-items-center rounded-full text-black hover:text-ink disabled:opacity-40">
                        <svg class="size-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 20.4l17.5-7.5a1 1 0 000-1.8L3.4 3.6a.9.9 0 00-1.3 1.1L4 10.5l9 1.5-9 1.5-1.9 5.8a.9.9 0 001.3 1.1z" /></svg>
                    </button>
                </div>
                <p x-show="error" x-cloak x-text="error" class="m-0 text-sm text-red-600"></p>
                @error('text')
                    <p class="m-0 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        @endauth
        @guest
            <p class="m-0">{{ $words[3] }}, необходимо
                <a class="font-bold text-black underline" href="{{ route('register') }}">зарегистрироваться</a>
                или <a class="font-bold text-black underline" href="{{ route('login') }}">войти</a></p>
        @endguest
    </div>
</section>
