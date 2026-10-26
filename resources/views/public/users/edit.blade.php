@extends('public.layouts.app')
@section('title', 'Профиль | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')
@section('wide', '1')

@section('hero')
    <x-ui.page-header title="Профиль" :items="[['Личный кабинет', route('cabinet.index')], ['Профиль', null]]" />
@endsection

@section('sidebar')
    @include('public.cabinet.nav', ['active' => 'profile'])
@endsection

@section('content')
    @php
        $passwordErrors = $errors->hasAny(['current_password', 'password']);
        $avatarUrl = $user->avatar ? Storage::url($user->avatar->uri) : null;
        $label = 'mb-1.5 block text-sm font-medium text-ink';
        $input = 'block h-10 w-full rounded-lg border border-line bg-white px-3 text-[15px] text-ink placeholder:text-subtle focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 aria-[invalid=true]:border-red-500';
        $err = 'mb-0 mt-1 text-[13px] text-red-600';
    @endphp
    {{-- Одна форма с двумя вкладками, как в админке: «Основное» и «Пароль», одна кнопка «Сохранить» --}}
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
        x-data="{ tab: @js($passwordErrors ? 'password' : 'main'), preview: @js($avatarUrl), remove: false, name: '' }">
        @csrf @method('PUT')
        <div class="rounded-xl border border-line bg-white">
            <div class="flex gap-1 border-b border-line px-3 pt-1 sm:px-4" role="tablist">
                @foreach (['main' => 'Основное', 'password' => 'Пароль'] as $key => $label)
                    <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-brand-400 text-black' : 'border-transparent text-subtle'"
                        class="-mb-px border-b-2 px-3 py-3 text-sm font-medium">{{ $label }}</button>
                @endforeach
            </div>

            <div x-show="tab === 'main'" role="tabpanel" class="grid gap-4 p-4">
                <div>
                    <label for="f-name" class="{{ $label }}">Имя</label>
                    <input id="f-name" name="name" type="text" value="{{ old('name', $user->name) }}" class="{{ $input }}" @error('name') aria-invalid="true" @enderror>
                    @error('name')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="f-email" class="{{ $label }}">Логин (email)</label>
                    <input id="f-email" type="email" value="{{ $user->email }}" readonly class="{{ $input }} bg-surface text-subtle">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="f-phone" class="{{ $label }}">Телефон</label>
                        <input id="f-phone" name="phone" type="text" x-data="phoneMask" value="{{ old('phone', $user->phone) }}" placeholder="+7 (900) 000-00-00" class="{{ $input }}" @error('phone') aria-invalid="true" @enderror>
                        @error('phone')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="f-location" class="{{ $label }}">Город</label>
                        <input id="f-location" name="location" type="text" value="{{ old('location', $user->location) }}" placeholder="Например: Уфа" class="{{ $input }}" @error('location') aria-invalid="true" @enderror>
                        @error('location')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="f-bio" class="{{ $label }}">О себе</label>
                    <textarea id="f-bio" name="bio" rows="4" maxlength="512" placeholder="Расскажите о себе, не более 512 символов" class="{{ $input }} h-auto resize-y py-2" @error('bio') aria-invalid="true" @enderror>{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<p class="{{ $err }}">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="{{ $label }}">Аватар</span>
                    <div class="flex items-center gap-3">
                        <img x-show="preview && !remove" :src="preview" alt="" class="size-10 shrink-0 rounded-full object-cover">
                        <span x-show="!preview || remove" class="grid size-10 shrink-0 place-items-center rounded-full bg-surface text-sm font-bold text-subtle">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div class="flex h-10 min-w-0 flex-1 items-stretch overflow-hidden rounded-lg border border-line">
                            <label for="f-avatar" class="flex cursor-pointer items-center whitespace-nowrap bg-brand-400/30 px-4 text-sm text-ink">Выберите файл</label>
                            <span class="min-w-0 flex-1 truncate px-3 text-sm leading-10 text-ink" x-text="name || 'Файл не выбран'">Файл не выбран</span>
                            <input id="f-avatar" name="avatar" type="file" accept="image/jpeg,image/png" class="sr-only"
                                @change="const f = $event.target.files[0]; if (f) { name = f.name; preview = URL.createObjectURL(f); remove = false }">
                        </div>
                    </div>
                    @error('avatar')<p class="{{ $err }}">{{ $message }}</p>@enderror
                    @if ($avatarUrl)
                        <label class="mt-3 flex cursor-pointer items-center gap-2 text-sm text-subtle">
                            <input type="checkbox" name="remove_avatar" value="1" x-model="remove" class="size-4 accent-black"> Удалить текущий аватар
                        </label>
                    @endif
                </div>
            </div>

            <div x-show="tab === 'password'" x-cloak role="tabpanel" class="grid gap-4 p-4">
                <p class="m-0 text-[13px] text-subtle">Оставьте поля пустыми, если пароль менять не нужно.</p>
                @foreach ([['current_password', 'Текущий пароль', 'current-password'], ['password', 'Новый пароль', 'new-password'], ['password_confirmation', 'Повторите новый пароль', 'new-password']] as [$name, $text, $auto])
                    <div>
                        <label for="f-{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                        <input id="f-{{ $name }}" name="{{ $name }}" type="password" autocomplete="{{ $auto }}" class="{{ $input }}" @error($name) aria-invalid="true" @enderror>
                        @error($name)<p class="{{ $err }}">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>

        </div>

        <button type="submit" class="mt-6 inline-flex h-11 items-center rounded-lg bg-black px-6 text-brand-400">Сохранить</button>
    </form>
@endsection
