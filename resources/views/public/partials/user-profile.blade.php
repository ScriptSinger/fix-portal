<div x-data="userProfile()"
    x-show="open"
    x-cloak
    @user-profile-open.document="openProfile($event.detail)"
    @keydown.escape.window="close()"
    @click="close()"
    class="fixed inset-0 z-[65]"
    role="presentation">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/45" @click="close()" aria-hidden="true"></div>

    <section x-ref="card"
        x-show="open"
        x-transition.opacity.scale.origin.top.left
        @click.stop
        role="dialog"
        aria-modal="true"
        aria-label="Профиль пользователя"
        aria-labelledby="user-profile-name"
        class="absolute bottom-0 left-0 w-full overflow-hidden rounded-t-2xl bg-white shadow-[0_8px_32px_rgba(0,0,0,.2)] sm:bottom-auto sm:left-1/2 sm:top-1/2 sm:w-[min(22.5rem,calc(100vw-1.5rem))] sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-2xl">
        <div x-show="loading" class="p-5" aria-live="polite" aria-label="Загрузка профиля">
            <div class="flex items-center gap-3">
                <span class="size-16 animate-pulse rounded-full bg-black/10"></span>
                <div class="min-w-0 flex-1 space-y-2">
                    <span class="block h-4 w-36 animate-pulse rounded bg-black/10"></span>
                    <span class="block h-3 w-24 animate-pulse rounded bg-black/10"></span>
                </div>
            </div>
            <div class="mt-5 space-y-2"><span class="block h-3 w-full animate-pulse rounded bg-black/10"></span><span class="block h-3 w-4/5 animate-pulse rounded bg-black/10"></span></div>
        </div>

        <div x-show="!loading && error" class="p-5" role="alert">
            <div class="flex items-start justify-between gap-4">
                <p class="m-0 text-sm text-red-600" x-text="error"></p>
                <button type="button" @click="close()" class="grid size-8 shrink-0 place-items-center rounded-full text-2xl leading-none text-subtle hover:bg-black/5" aria-label="Закрыть профиль">&times;</button>
            </div>
        </div>

        <template x-if="!loading && !error && data">
            <div>
                <div class="relative bg-black px-5 pb-5 pt-4 text-white sm:pt-5">
                    <button type="button" @click="close()" class="absolute right-3 top-3 grid size-9 place-items-center rounded-full text-2xl leading-none text-white/75 hover:bg-white/15 hover:text-white" aria-label="Закрыть профиль">&times;</button>
                    <div class="flex items-end gap-3 pr-9">
                        <img :src="data.avatar_url" :alt="data.name" class="size-16 shrink-0 rounded-full object-cover ring-2 ring-white/80" loading="eager">
                        <div class="min-w-0 pb-0.5">
                            <h2 id="user-profile-name" class="m-0 truncate text-lg font-bold" x-text="data.name"></h2>
                            <p class="m-0 truncate text-sm text-white/70">@<span x-text="data.username"></span></p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-xs text-white/75">
                        <span class="size-2 rounded-full" :class="data.is_online ? 'bg-green-400' : 'bg-white/35'"></span>
                        <span x-text="data.is_online ? 'В сети' : 'Был(а) недавно'"></span>
                    </div>
                </div>
                <div class="space-y-4 p-5">
                    <p x-show="data.bio" class="m-0 whitespace-pre-line text-sm leading-5 text-ink" x-text="data.bio"></p>
                    <div x-show="data.location || data.registered_at" class="space-y-1 text-xs text-subtle">
                        <p x-show="data.location" class="m-0"><span aria-hidden="true">⌖</span> <span x-text="data.location"></span></p>
                        <p x-show="data.registered_at" class="m-0"><span aria-hidden="true">◷</span> На сайте с <span x-text="formatDate(data.registered_at)"></span></p>
                    </div>
                    <dl class="grid grid-cols-3 divide-x divide-line border-y border-line py-3 text-center">
                        <div><dt class="text-xs text-subtle">Вопросы</dt><dd class="m-0 text-lg font-bold text-ink" x-text="data.statistics.questions"></dd></div>
                        <div><dt class="text-xs text-subtle">Ответы</dt><dd class="m-0 text-lg font-bold text-ink" x-text="data.statistics.answers"></dd></div>
                        <div><dt class="text-xs text-subtle">Решения</dt><dd class="m-0 text-lg font-bold text-ink" x-text="data.statistics.solutions"></dd></div>
                    </dl>
                </div>
            </div>
        </template>
    </section>
</div>
