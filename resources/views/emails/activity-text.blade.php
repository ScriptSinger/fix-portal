Здравствуйте, {{ $user->name }}!

@foreach ($items as $item)
@if ($item['kind'] === \App\Notifications\ActivityNotification::SOLUTION)
Ваш ответ в вопросе «{{ $item['title'] }}» отмечен решением. Спасибо за помощь!
@else
{{ $item['actor_name'] }} ответил(а) на ваш вопрос «{{ $item['title'] }}»:
@endif
@if (! empty($item['snippet']))
«{{ $item['snippet'] }}»
@endif
Открыть: {{ $item['url'] }}

@endforeach
--
Вы получили это письмо, потому что задавали вопросы или оставляли ответы на {{ config('app.name', 'Ufamasters') }}.
Отключить такие письма: {{ $unsubscribeUrl }}
Настройки профиля: {{ $profileUrl }}
