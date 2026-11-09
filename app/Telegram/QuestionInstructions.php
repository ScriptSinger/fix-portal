<?php

namespace App\Telegram;

class QuestionInstructions
{
    public static function registered(): string
    {
        return "✅ Вопрос зарегистрирован.\n\nКогда получите решение, поставьте 🤝 на сообщение с ответом. Затем подтвердите, какой вопрос решён. Помощник получит +1 к карме.";
    }

}
