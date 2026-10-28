@extends('emails.layout')
@section('title', 'Новое на ' . config('app.name', 'Ufamasters'))

@section('content')
    <p style="margin:0 0 16px;">Здравствуйте, {{ $user->name }}!</p>

    @foreach ($items as $item)
        <div style="margin:0 0 20px;padding:0 0 20px;border-bottom:1px solid #eeeeee;">
            @if ($item['kind'] === \App\Notifications\ActivityNotification::SOLUTION)
                <p style="margin:0 0 8px;"><b>Ваш ответ в вопросе «{{ $item['title'] }}» отмечен решением.</b> Спасибо за помощь!</p>
            @else
                <p style="margin:0 0 8px;"><b>{{ $item['actor_name'] }}</b> ответил(а) на ваш вопрос «{{ $item['title'] }}»:</p>
            @endif

            @if (! empty($item['snippet']))
                <p style="margin:0 0 12px;padding:10px 14px;background:#f7f7f7;border-left:3px solid #f9ca27;color:#555555;">{{ $item['snippet'] }}</p>
            @endif

            <a href="{{ $item['url'] }}" style="display:inline-block;padding:10px 22px;background:#111111;color:#f9ca27;text-decoration:none;border-radius:20px;font-weight:bold;">Открыть</a>
        </div>
    @endforeach
@endsection

@section('footer')
    <p style="margin:0 0 6px;">Вы получили это письмо, потому что задавали вопросы или оставляли ответы на {{ config('app.name', 'Ufamasters') }}.</p>
    <p style="margin:0;">
        <a href="{{ $unsubscribeUrl }}" style="color:#888888;">Отключить такие письма</a>
        &nbsp;·&nbsp;
        <a href="{{ $profileUrl }}" style="color:#888888;">Настройки профиля</a>
    </p>
@endsection
