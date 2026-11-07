<?php

namespace App\Actions\SitePublication;

use Illuminate\Support\Facades\DB;

trait RecordsModerationActions
{
    private function audit(?int $moderatorId, string $action, array $parameters): void
    {
        $parameters['source'] = $moderatorId === null ? 'moonshine' : 'telegram';

        DB::table('telegram_moderation_actions')->insert([
            'moderator_telegram_id' => $moderatorId ?? 0,
            'user_id' => null,
            'action' => $action,
            'parameters' => json_encode($parameters, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
