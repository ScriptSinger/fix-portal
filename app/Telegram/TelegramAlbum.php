<?php

namespace App\Telegram;

use Illuminate\Support\Facades\DB;

class TelegramAlbum
{
    /** @return list<array<int, array<string, mixed>>> */
    public function collect(array $reply, string $mediaGroupId): array
    {
        $groups = [];
        $seen = [];

        $add = static function (mixed $photos) use (&$groups, &$seen): void {
            if (! is_array($photos) || $photos === []) {
                return;
            }

            $largest = end($photos);
            $key = (string) data_get($largest, 'file_unique_id', data_get($largest, 'file_id', ''));
            if ($key === '' || isset($seen[$key])) {
                return;
            }

            $seen[$key] = true;
            $groups[] = $photos;
        };

        $add($reply['photo'] ?? null);

        foreach (['telegram_webhook_updates', 'telegram_question_messages'] as $table) {
            DB::table($table)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.message.media_group_id')) = ?", [$mediaGroupId])
                ->get(['payload'])
                ->each(function (object $row) use ($add): void {
                    $add(data_get(json_decode((string) $row->payload, true), 'message.photo'));
                });
        }

        return $groups;
    }

}
