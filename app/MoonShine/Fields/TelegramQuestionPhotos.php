<?php

declare(strict_types=1);

namespace App\MoonShine\Fields;

use App\Models\TelegramQuestionMessage;
use MoonShine\UI\Fields\Preview;

class TelegramQuestionPhotos
{
    public static function make(): Preview
    {
        return Preview::make('Фотографии', 'payload', static function (TelegramQuestionMessage $item): string {
            $photos = $item->telegramPhotoFileIds();
            if ($photos === []) {
                return '<span class="text-gray-500">Нет прикреплённых фотографий</span>';
            }

            return '<div style="display:flex;flex-wrap:wrap;gap:12px;max-width:900px">'.collect($photos)
                ->values()
                ->map(static fn (string $fileId, int $index): string => '<a href="'.e(route('moonshine.telegram-question-photo', [$item->id, $index])).'" target="_blank" rel="noopener"><img src="'.e(route('moonshine.telegram-question-photo', [$item->id, $index])).'" alt="Фото '.($index + 1).'" style="width:180px;height:180px;object-fit:cover;border-radius:8px;border:1px solid #ddd"></a>')
                ->implode('').'</div>';
        });
    }
}
