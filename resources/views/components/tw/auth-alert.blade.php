@props(['register' => false])
<div class="mb-4 rounded bg-[#ffc107] px-5 py-3 text-[80%] leading-6 text-[#1f2d3d]">
    <strong>Внимание:</strong> Вход через Google отключен.
    @if ($register)
        Если у вас уже есть аккаунт, созданный через Google, воспользуйтесь функцией
    @else
        Если вы ранее регистрировались через Google, пожалуйста, воспользуйтесь функцией
    @endif
    <a href="{{ route('password.request') }}" class="font-bold underline">«Забыли пароль?»</a>, чтобы установить пароль для входа в личный кабинет.
</div>
