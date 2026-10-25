@extends('public.layouts.app')
@section('title', 'Личный кабинет | ' . config('app.name', 'Ufamasters'))
@section('robots', 'noindex, nofollow')

@section('hero')
    <x-ui.page-header :title="'Добро пожаловать, ' . $user->name" :items="[ ['Личный кабинет', null]]" />
@endsection

@section('content')
    <div class="rounded-[5px] bg-white p-12 max-sm:px-4" x-data="{ tab: 'data' }">
        <div class="mb-6 flex border-b border-soft" role="tablist">
            @foreach (['data' => 'Личные данные', 'image' => 'Изображения'] as $key => $label)
                <button type="button" role="tab" @click="tab = '{{ $key }}'" :aria-selected="tab === '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-soft border-b-white bg-white text-black' : 'border-transparent text-body hover:border-[#eceeef]'"
                    class="-mb-px border px-4 py-2">{{ $label }}</button>
            @endforeach
        </div>

        <div x-show="tab === 'data'" role="tabpanel">
            <div class="mb-6 grid gap-6 md:grid-cols-2">
                <div>
                    <h4 class="mb-2 text-[19px] font-bold leading-[29px] text-black">Мастер</h4>
                    <p class="mb-0">Если вы являетесь специалистом по ремонту бытовой техники, установите статус "мастера", чтобы ваши данные были доступны в разделе мастеров.</p>
                </div>
                <div>
                    <h4 class="mb-2 text-[19px] font-bold leading-[29px] text-black">Участник</h4>
                    <p class="mb-0">Для ограничения доступа к вашим данным, выберите статус "участника", который не позволит публиковать вашу информацию.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PUT')
                <x-ui.field name="name" label="Имя" :value="$user->name" />
                <x-ui.select name="role_id" label="Статус" :options="$roles" :value="$user->role_id" />
                <div class="mb-6">
                    <label class="mb-2 block">Email</label>
                    <input disabled type="text" placeholder="{{ $user->email }}" class="field">
                </div>
                <x-ui.field name="phone" label="Телефон" :value="$user->phone" x-data="phoneMask" />
                <x-ui.field name="location" label="Город" :value="$user->location" placeholder="Местоположение" />
                <div class="mb-6">
                    <label for="f-bio" class="mb-2 block">О себе</label>
                    <textarea id="f-bio" name="bio" rows="4" placeholder="не более 512 символов"
                        class="field">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<p class="mt-1 text-[13px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-ui.button variant="dark">Обновить</x-ui.button>
            </form>
        </div>

        <div x-show="tab === 'image'" x-cloak role="tabpanel"
            x-data="avatarUpload({ showUrl: @js(route('api.avatars.show')), uploadUrl: @js(route('api.avatars.upload')), destroyUrl: @js(route('api.avatars.destroy')) })">
            <label @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop.prevent="over = false; pick($event.dataTransfer.files)"
                :class="over ? 'border-black bg-[#f1f1f1]' : 'border-soft'"
                class="mb-4 flex min-h-[150px] cursor-pointer flex-col items-center justify-center gap-3 border-2 border-dashed p-6 text-center">
                <template x-if="preview || current">
                    <img :src="preview || current.uri" alt="Аватар" class="size-28 rounded-full object-cover">
                </template>
                <span x-show="!preview && !current">Перетащите файл сюда или нажмите для выбора</span>
                <span x-show="file" x-text="file?.name" class="text-[13px]"></span>
                <input type="file" accept="image/jpeg,image/png" class="sr-only" @change="pick($event.target.files)">
            </label>
            <p x-show="error" x-cloak x-text="error" class="mb-3 text-sm text-red-600"></p>
            <p x-show="done" x-cloak class="mb-3 text-sm text-success">Аватар сохранён</p>
            <div class="flex gap-3">
                <x-ui.button variant="dark" type="button" x-on:click="send()" x-bind:disabled="!file || busy">Отправить</x-ui.button>
                <x-ui.button variant="outline" type="button" x-show="file || current" x-on:click="remove()">Удалить</x-ui.button>
            </div>
        </div>
    </div>
@endsection

@section('sidebar')
    @include('public.partials.sidebar', ['variant' => 'tags'])
@endsection
