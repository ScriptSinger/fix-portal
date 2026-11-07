<?php

namespace App\Jobs;

use App\Services\TelegramBotApi;
use App\Services\TelegramReputation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTelegramMemberTag implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $telegramChatId, public int $telegramUserId)
    {
        $this->afterCommit();
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(TelegramBotApi $api, TelegramReputation $reputation): void
    {
        $member = $api->call('getChatMember', [
            'chat_id' => $this->telegramChatId,
            'user_id' => $this->telegramUserId,
        ]);
        $status = $member['status'] ?? null;
        if (in_array($status, ['creator', 'administrator', 'left', 'kicked'], true)) {
            return;
        }
        if (! in_array($status, ['member', 'restricted'], true)) {
            throw new \RuntimeException('Telegram returned an unknown member status.');
        }

        // Recompute on every attempt so an old queued job cannot restore an old rank.
        $api->call('setChatMemberTag', [
            'chat_id' => $this->telegramChatId,
            'user_id' => $this->telegramUserId,
            'tag' => $reputation->rankFor($this->telegramUserId) ?? '',
        ]);
    }
}
