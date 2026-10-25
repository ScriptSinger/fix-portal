@props(['customization' => null])
{{-- Баннер главной: фото, заголовок и описание из настроек (админка), кнопка «Создать вопрос» --}}
<section aria-labelledby="hero-title" class="bg-cover bg-center py-16 lg:py-24"
    style="background-image: linear-gradient(90deg, rgba(0,0,0,.78), rgba(0,0,0,.25)), url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}')">
    <div class="site-container">
        <div class="max-w-[640px]">
            <h1 id="hero-title" class="mb-6 text-[48px] font-bold leading-[52px] text-white max-sm:text-[34px] max-sm:leading-10">
                {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
            <p class="mb-8 text-base leading-7 text-white">
                {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
            </p>
            <x-ui.button variant="accent" size="lg" :href="route('questions.create')">Создать вопрос</x-ui.button>
        </div>
    </div>
</section>
