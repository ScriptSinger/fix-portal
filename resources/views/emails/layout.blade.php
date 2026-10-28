<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Ufamasters'))</title>
</head>
{{-- Письма читаются в почтовых программах без нашего CSS: стили только встроенные, таблицы вместо сетки --}}
<body style="margin:0;padding:0;background:#f1f1f1;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f1f1;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
                <tr>
                    <td style="background:#f9ca27;padding:18px 24px;border-radius:12px 12px 0 0;font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:bold;color:#111111;">
                        {{ config('app.name', 'Ufamasters') }}
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff;padding:24px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:24px;color:#333333;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="background:#ffffff;padding:0 24px 24px;border-radius:0 0 12px 12px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:18px;color:#888888;">
                        @yield('footer')
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
