@props(['searchTitle', 'searchText', 'placeholder', 'action', 'field' => 'text', 'label' => 'Поиск', 'customization' => null])
<section class="bg-cover bg-center pb-32 pt-[119px] lg:pt-[115px]"
    style="background-image: url('{{ optional($customization)->getImage('banner') ?? asset('assets/front/images/power_unit.jpg') }}')">
    <div class="mx-auto sm:max-w-[540px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1140px] px-[15px]">
        <div class="-mx-[15px] grid items-center lg:grid-cols-[2fr_1fr]">
            <div class="px-[15px]">
                <h1 class="mb-8 text-[48px] font-bold leading-[44px] text-white max-sm:text-[34px]">
                    {{ optional($customization)->title ?? 'Ремонт бытовой техники' }}</h1>
                <p class="mb-8 text-base leading-7 text-white lg:mr-16">
                    {{ optional($customization)->description ?? 'Узнайте как решить проблемы с бытовой техникой от опытных пользователей. Регистрируйтесь для создания своего вопроса.' }}
                </p>
                <a href="{{ route('questions.create') }}"
                    class="btn inline-block bg-black px-8 py-[12.8px] text-sm font-bold leading-[17.5px] text-white">Создать вопрос</a>
            </div>

            <form method="GET" role="search" action="{{ $action }}"
                class="relative mx-[15px] mt-8 lg:mt-0 border-[6px] border-brand-400 bg-brand-400 px-8 py-12 text-center before:pointer-events-none before:absolute before:inset-0 before:border before:border-black">
                <h2 class="mb-[17px] text-[22px] font-bold leading-[30.8px] text-black">{{ $searchTitle }}</h2>
                <p class="mb-8 text-black/90">{{ $searchText }}</p>
                <label for="hero-search" class="sr-only">{{ $label }}</label>
                <input id="hero-search" name="{{ $field }}" type="search" value="{{ request($field) }}" placeholder="{{ $placeholder }}"
                    class="mb-6 block h-14 w-full border border-[#dbdde0] sm:mb-4 bg-white py-2 pl-6 pr-3 text-sm text-[#464a4c] placeholder:text-[#464a4c] focus:outline-none">
                <button type="submit"
                    class="block h-[54px] w-full bg-black px-8 text-sm font-bold leading-[17.5px] text-white">Найти</button>
            </form>
        </div>
    </div>
</section>
