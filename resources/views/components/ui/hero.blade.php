@props(['customization' => null])
{{-- Баннер главной в границах полей контента: фото, заголовок и описание из настроек (админка) --}}
<section aria-labelledby="hero-title" class="mb-6 flex min-h-[380px] items-center rounded-card bg-cover bg-center px-6 py-14 sm:px-12 lg:min-h-[420px]"
    {{-- затемнение слева направо: подпись читается на любой фотографии, правая часть фото остаётся яркой --}}
    style="background-image: linear-gradient(90deg, rgba(0,0,0,.68) 0%, rgba(0,0,0,.5) 55%, rgba(0,0,0,.15) 100%), url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}')">
    <div class="max-w-[640px] [text-shadow:0_1px_10px_rgba(0,0,0,.5)]">
        <h1 id="hero-title" class="mb-6 text-[48px] font-bold leading-[52px] text-white max-sm:text-[34px] max-sm:leading-10">
            {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
        <p class="mb-0 text-base leading-7 text-white">
            {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
        </p>
    </div>
</section>
