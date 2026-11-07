<?php

namespace App\Telegram;

class QuestionInstructions
{
    public static function registered(): string
    {
        return "✅ Вопрос зарегистрирован.\n\nКогда получите решение, поставьте 🤝 на сообщение с ответом. Вопрос станет решённым, а помощник получит +1 к карме.";
    }

}
