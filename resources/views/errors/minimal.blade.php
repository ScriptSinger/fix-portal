<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') | Ufamasters</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;flex-direction:column;background:#f1f1f1;color:#5e5e5e;font:15px/26px 'Roboto Slab',Georgia,serif}
        header{background:#f9ca27;border:6px solid #f9ca27;box-shadow:inset 0 0 0 1px #000;padding:14px 24px}
        header a{color:#111;font-weight:700;font-size:20px;text-decoration:none}
        main{flex:1;display:flex;align-items:center;justify-content:center;padding:32px 16px}
        .card{background:#fff;border-radius:5px;padding:48px 32px;max-width:560px;text-align:center}
        .code{font-size:72px;font-weight:700;line-height:1;color:#f9ca27;margin:0}
        h1{font-size:28px;line-height:36px;color:#000;margin:16px 0 8px}
        .btn{display:inline-block;margin-top:20px;background:#000;color:#f9ca27;padding:12px 24px;font-weight:700;text-decoration:none}
    </style>
</head>
<body>
    <header><a href="/">Ufamasters</a></header>
    <main>
        <div class="card">
            <p class="code">@yield('code')</p>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <a class="btn" href="/">На главную</a>
        </div>
    </main>
</body>
</html>
