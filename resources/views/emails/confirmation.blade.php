<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение регистрации</title>
</head>

<body>
    <h1>Добро пожаловать, {{ $user->name }}!</h1>

    <p>
        Спасибо за регистрацию на нашем сайте. Мы рады видеть вас в нашем сообществе.
    </p>

    @if ($verificationUrl)
        <p>
            Для завершения регистрации пройдите по <a href="{{ $verificationUrl }}">ссылке</a>.
        </p>
    @endif

    <p>
        С уважением, {{ config('app.name', 'Ufamasters') }}
    </p>
</body>

</html>
