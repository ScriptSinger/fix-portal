@php
    [$body, $photos] = \App\Support\ContentImages::extract($message->text);
    $body = \App\Support\HtmlCleanup::stripInlineStyles($body);
    $label = $message instanceof \App\Models\Reply ? 'Ответ' : 'Комментарий';
@endphp
<div data-message-content>
    @if ($photos)
        <x-ui.photo-grid :photos="$photos" :alt="$label" class="my-1 rounded-lg" />
    @endif
    <div data-message-body class="article-content {{ $body === strip_tags($body) ? 'whitespace-pre-line' : '' }} text-[15px] leading-[1.45] [overflow-wrap:anywhere]">@if ($body !== ''){!! \App\Support\Linkify::html(\App\Support\ContentImages::withAlt($body, $label)) !!}@endif</div>
</div>
