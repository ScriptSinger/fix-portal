{{-- Жёлтый кружок с карандашом, который «пишет» строку (анимация в app.css); в форме вопроса и в приглашении для гостей --}}
<span {{ $attributes->class('grid size-8 shrink-0 place-items-center rounded-full bg-brand-400 text-black') }} aria-hidden="true">
    <svg class="size-5" viewBox="0 0 24 24" aria-hidden="true">
        <path class="pencil-line" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" d="M2 20.5c1-1.8 2-1.8 3 0s2 1.8 3 0 2-1.8 3 0" pathLength="1" />
        <g class="pencil-body"><path fill="currentColor" transform="translate(2 20)" d="M0 0l1.3-3.4 7.4-7.4a1.3 1.3 0 011.8 0l.7.7a1.3 1.3 0 010 1.8l-7.4 7.4z" /></g>
    </svg>
</span>
