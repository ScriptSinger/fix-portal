@props(['title', 'lead' => null, 'eyebrow' => 'UfaMasters', 'headline', 'points' => []])
{{-- Экран входа: слева жёлтая панель с пользой аккаунта, справа форма. На телефоне панель сжимается до полосы. --}}
<div class="mx-auto grid w-full max-w-[920px] overflow-hidden rounded-[5px] bg-white shadow-[0_2px_20px_rgba(0,0,0,.08)] max-sm:rounded-none md:grid-cols-[1fr_1.15fr]">
    <aside class="relative flex flex-col justify-start gap-10 bg-brand-400 p-10 text-black max-md:p-6">
        <div>
            <p class="mb-3 mt-0 text-[13px] font-bold uppercase tracking-[2px] max-md:hidden">{{ $eyebrow }}</p>
            <p class="mb-0 text-[28px] font-bold leading-[36px] max-md:mt-4 max-md:text-[20px] max-md:leading-7">{{ $headline }}</p>
        </div>
        <ul class="mb-0 space-y-4 max-md:hidden">
            @foreach ($points as $point)
                <li class="flex items-start gap-3 text-[15px] leading-6">
                    <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center bg-black text-brand-400" aria-hidden="true">
                        <x-ui.icon name="check" class="size-4" />
                    </span>
                    <span>{{ $point }}</span>
                </li>
            @endforeach
        </ul>
    </aside>

    <section class="p-10 max-md:p-6">
        <h1 class="mb-2 text-[30px] font-bold leading-[38px] text-black">{{ $title }}</h1>
        @if ($lead)<p class="mb-8">{{ $lead }}</p>@else<div class="mb-8"></div>@endif
        {{ $slot }}
    </section>
</div>
