<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Вход через Яндекс ID и VK ID. Адрес возврата: {APP_URL}/auth/yandex/callback и {APP_URL}/auth/vkid/callback
    'yandex' => [
        'client_id' => env('YANDEX_CLIENT_ID'),
        'client_secret' => env('YANDEX_CLIENT_SECRET'),
        'redirect' => env('YANDEX_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/auth/yandex/callback'),
    ],

    'vkid' => [
        'client_id' => env('VKID_CLIENT_ID'),
        'client_secret' => env('VKID_CLIENT_SECRET'),
        'redirect' => env('VKID_REDIRECT_URI', rtrim((string) env('APP_URL'), '/').'/auth/vkid/callback'),
    ],

    // Бот пишет новые вопросы в чат мастеров. Пока токен или чат пусты, канал выключен.
    // Telegram недоступен с сервера, поэтому запросы идут через HTTP-прокси (TELEGRAM_PROXY=http://логин:пароль@хост:порт)
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        // Номер темы, если в группе включены темы (форум); без него сообщения идут в «Общее»
        'thread_id' => env('TELEGRAM_THREAD_ID'),
        'proxy' => env('TELEGRAM_PROXY'),
        'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL', rtrim((string) env('APP_URL'), '/') . '/api/telegram/webhook'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'environment' => env('TELEGRAM_ENVIRONMENT', env('APP_ENV', 'local')),
        'moderator_ids' => array_values(array_filter(array_map(
            static fn (string $id): int => (int) trim($id),
            explode(',', (string) env('TELEGRAM_MODERATOR_IDS', ''))
        ), static fn (int $id): bool => $id > 0)),
        'mute_minutes' => (int) env('TELEGRAM_MUTE_MINUTES', 1440),
        'source_chat_id' => env('TELEGRAM_SOURCE_CHAT_ID'),
        'source_thread_id' => env('TELEGRAM_SOURCE_THREAD_ID'),
        'silent_source_commands' => filter_var(env('TELEGRAM_SILENT_SOURCE_COMMANDS', true), FILTER_VALIDATE_BOOL),
        'moderation_chat_id' => env('TELEGRAM_MODERATION_CHAT_ID'),
        'moderation_thread_id' => env('TELEGRAM_MODERATION_THREAD_ID'),
        'author_user_id' => (int) env('TELEGRAM_AUTHOR_USER_ID', 0),
        'command_rate_limit_per_minute' => (int) env('TELEGRAM_COMMAND_RATE_LIMIT_PER_MINUTE', 10),
        'max_pending_per_user' => (int) env('TELEGRAM_MAX_PENDING_PER_USER', 5),
    ],

    // Стена VK-группы: сюда уходят новые вопросы. Ключ доступа сообщества с правом на стену, номер группы без минуса.
    // Пока токен или группа пусты, канал выключен. Не путать с vkid выше: это вход на сайт.
    'vk_group' => [
        'token' => env('VK_GROUP_TOKEN'),
        'group_id' => env('VK_GROUP_ID'),
        'version' => env('VK_API_VERSION', '5.199'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

];
