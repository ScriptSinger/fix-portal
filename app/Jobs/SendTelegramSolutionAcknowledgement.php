<?php

namespace App\Jobs;

use App\Services\TelegramBotApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramSolutionAcknowledgement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 30;

    public function __construct(public int $chatId, public int $messageId)
    {
        // The database queue shares the default DB connection with the award.
        // Commit or roll back the solution and its delivery task together.
        $this->onConnection('database');
        $this->beforeCommit();
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(TelegramBotApi $api): void
    {
        // Setting the same reaction again does not award karma or add a second reaction.
        $api->call('setMessageReaction', [
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
            'reaction' => [['type' => 'emoji', 'emoji' => '🎉']],
        ]);
    }
}
