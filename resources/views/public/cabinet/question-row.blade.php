<div class="flex items-start gap-3 border-b border-line py-3.5 last:border-0">
    <div class="min-w-0 flex-1">
        <a href="{{ route('questions.show', ['question' => $question->slug]) }}" class="keep font-bold text-black hover:underline">{{ $question->title }}</a>
        <div class="text-[13px] text-subtle">{{ optional($question->appliance)->title }} · {{ $question->dateAsCarbon->diffForHumans() }} · {{ $question->views }} {{ trans_choice('просмотр|просмотра|просмотров', $question->views, [], 'ru') }}</div>
    </div>
    @if ($question->comments_count)
        <span class="shrink-0 rounded-full bg-black/5 px-2.5 py-0.5 text-xs font-medium">{{ $question->comments_count }} {{ trans_choice('ответ|ответа|ответов', $question->comments_count, [], 'ru') }}</span>
    @else
        <span class="shrink-0 rounded-full bg-brand-400/40 px-2.5 py-0.5 text-xs font-medium">без ответа</span>
    @endif
</div>
