<?php

namespace App\Telegram;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\RunningMode;
use SergiX44\Nutgram\Telegram\Types\Common\Update;

/** Nutgram routing after our webhook authentication and update deduplication. */
class PersistedUpdate implements RunningMode
{
    public function __construct(private array $payload) {}

    public function processUpdates(Nutgram $bot): void
    {
        $bot->processUpdate(Update::fromArray($this->payload));
    }
}
