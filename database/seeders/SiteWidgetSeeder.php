<?php

namespace Database\Seeders;

use App\Models\SiteWidget;
use Illuminate\Database\Seeder;

class SiteWidgetSeeder extends Seeder
{
    public function run(): void
    {
        $widgets = [
            [
                'key' => 'ads',
                'title' => 'РСЯ',
                'description' => 'Рекламный блок Яндекс РСЯ',
                'enabled' => false,
                'block_id' => 'R-A-1966455-2',
                'url' => null,
                'display_limit' => null,
                'sort' => 1,
            ],
            [
                'key' => 'repair',
                'title' => 'Ремонт',
                'description' => 'Партнёрский виджет сервиса ремонта',
                'enabled' => true,
                'block_id' => null,
                'url' => 'https://service.ufamasters.ru',
                'display_limit' => null,
                'sort' => 2,
            ],
            [
                'key' => 'telegram',
                'title' => 'Telegram-чат',
                'description' => 'Приглашение в Telegram-чат сообщества',
                'enabled' => true,
                'block_id' => null,
                'url' => 'https://t.me/ufamasters_chat',
                'display_limit' => null,
                'sort' => 3,
            ],
            [
                'key' => 'latest_discussions',
                'title' => 'Последние обсуждения',
                'description' => 'Свежие комментарии и ответы',
                'enabled' => true,
                'block_id' => null,
                'url' => null,
                'display_limit' => 5,
                'sort' => 4,
            ],
        ];

        foreach ($widgets as $widget) {
            $key = $widget['key'];
            unset($widget['key']);

            SiteWidget::query()->firstOrCreate(['key' => $key], $widget);
        }
    }
}
