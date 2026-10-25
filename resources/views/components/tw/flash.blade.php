@php $flash = session('success') ? ['success', session('success')] : (session('error') ? ['error', session('error')] : null); @endphp
@if ($flash)
    {{-- Замена SweetAlert2: уведомление в правом верхнем углу на 3 секунды, пауза при наведении --}}
    <div x-data="{ show: true, timer: null, hide() { this.timer = setTimeout(() => this.show = false, 3000) } }" x-init="hide()"
        x-show="show" x-transition.opacity @mouseenter="clearTimeout(timer)" @mouseleave="hide()" role="status"
        class="fixed right-4 top-4 z-[80] flex max-w-sm items-center gap-3 rounded-[5px] bg-white px-4 py-3 shadow-[0_4px_12px_rgba(0,0,0,.25)]">
        <span @class(['flex size-8 shrink-0 items-center justify-center rounded-full border-2', 'border-[#28a745] text-[#28a745]' => $flash[0] === 'success', 'border-[#dc3545] text-[#dc3545]' => $flash[0] === 'error']) aria-hidden="true">{{ $flash[0] === 'success' ? '✓' : '✕' }}</span>
        <span class="text-[15px] leading-5 text-body"><strong class="block text-black">{{ $flash[0] === 'success' ? 'Успешно!' : 'Ошибка!' }}</strong>{{ $flash[1] }}</span>
        <button type="button" @click="show = false" class="ml-2 text-xl leading-none text-black/40 hover:text-black" aria-label="Закрыть">&times;</button>
    </div>
@endif
