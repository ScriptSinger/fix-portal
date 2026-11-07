<?php

namespace App\Telegram;

class QuestionInstructions
{
    public static function registered(): string
    {
        return "✅ Вопрос зарегистрирован.\nЧтобы отметить решение, поставьте 👍 на сообщение участника, чей ответ помог.";
    }
}
