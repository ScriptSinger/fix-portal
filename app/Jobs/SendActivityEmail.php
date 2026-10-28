<?php

namespace App\Jobs;

use App\Mail\ActivityDigest;
use App\Models\User;
use App\Services\ActivityMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Отправляет человеку одно письмо с тем, что накопилось и что он ещё не прочитал на сайте. */
class SendActivityEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function __construct(public int $userId)
    {
    }

    public function handle(ActivityMailer $mailer): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $mailer->closeWindow($user);

        if (! $mailer->enabled() || ! $mailer->wantsMail($user)) {
            return;
        }

        $notifications = $mailer->pending($user);

        // за это время человек уже всё прочитал на сайте: письмо не нужно
        if ($notifications->isEmpty()) {
            return;
        }

        if (! $mailer->withinLimits($user)) {
            Log::warning("Письмо об активности пользователю {$user->id} не отправлено: достигнут суточный потолок.");

            return;
        }

        Mail::to($user->email)->send(new ActivityDigest($user, $notifications->map(fn ($n) => $n->data)->all()));

        $mailer->markSent($user, $notifications);
    }
}
