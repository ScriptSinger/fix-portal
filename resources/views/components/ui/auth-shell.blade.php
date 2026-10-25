@props(['title', 'lead' => null])
{{-- Карточка формы входа и регистрации внутри общего макета сайта --}}
<div class="mx-auto w-full max-w-[480px] rounded-[5px] bg-white p-12 max-sm:px-5">
    @if ($lead)<p class="mb-8">{{ $lead }}</p>@endif
    {{ $slot }}
</div>
