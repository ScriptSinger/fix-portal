<x-ui.widget title="Свежие вопросы">
    <ul class="mb-4">
        @foreach ($latest_questions as $question)
            <li class="border-b border-dashed border-line py-3 first:pt-0 last:border-0">
                <a href="{{ route('questions.show', ['question' => $question->slug]) }}" class="block text-[15px] font-bold leading-[22px] text-black">{{ $question->title }}</a>
                <span class="mt-1 block text-xs leading-5 text-subtle">{{ $question->appliance->title }} · {{ $question->dateAsCarbon->diffForHumans() }}</span>
            </li>
        @endforeach
    </ul>
    <x-ui.button variant="accent" :href="route('questions.create')" class="w-full">Задать вопрос</x-ui.button>
    <a href="{{ route('questions.index') }}" class="mt-3 block py-2 text-center text-[13px] font-bold text-ink underline">Все вопросы</a>
</x-ui.widget>
