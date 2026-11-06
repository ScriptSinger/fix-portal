<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramUpdateHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    private const MAX_PAYLOAD_BYTES = 262144;

    public function __invoke(Request $request, TelegramUpdateHandler $handler): JsonResponse
    {
        if ((string) config('services.telegram.environment') !== app()->environment()) {
            Log::critical('Telegram webhook environment mismatch', [
                'configured' => config('services.telegram.environment'),
                'application' => app()->environment(),
            ]);

            return response()->json(['message' => 'Telegram environment is misconfigured'], 503);
        }

        $secret = (string) config('services.telegram.webhook_secret');
        $providedSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token');

        if ($secret === '' || $providedSecret === '' || ! hash_equals($secret, $providedSecret)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $rawPayload = $request->getContent();
        if (strlen($rawPayload) > self::MAX_PAYLOAD_BYTES) {
            return response()->json(['message' => 'Payload too large'], 413);
        }

        try {
            $payload = json_decode($rawPayload, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        if (! is_array($payload) || ! isset($payload['update_id']) || ! is_int($payload['update_id'])) {
            return response()->json(['message' => 'Invalid Telegram update'], 422);
        }

        $inserted = DB::table('telegram_webhook_updates')->insertOrIgnore([
            'update_id' => $payload['update_id'],
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'received_at' => now(),
        ]);

        if ($inserted === 0) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        Log::info('Telegram webhook update accepted', [
            'update_id' => $payload['update_id'],
            'update_types' => array_values(array_intersect(
                ['message', 'edited_message', 'callback_query', 'my_chat_member'],
                array_keys($payload)
            )),
        ]);

        try {
            DB::table('telegram_webhook_updates')
                ->where('update_id', $payload['update_id'])
                ->increment('attempts');

            $handler->handle($payload['update_id'], $payload);

            DB::table('telegram_webhook_updates')
                ->where('update_id', $payload['update_id'])
                ->update([
                    'failed_at' => null,
                    'processing_error' => null,
                ]);
        } catch (\Throwable $exception) {
            Log::error('Telegram webhook update handling failed', [
                'update_id' => $payload['update_id'],
                'error' => $exception->getMessage(),
            ]);

            DB::table('telegram_webhook_updates')
                ->where('update_id', $payload['update_id'])
                ->update([
                    'failed_at' => now(),
                    'processing_error' => mb_substr($exception->getMessage(), 0, 2000),
                ]);
        }

        return response()->json(['ok' => true]);
    }
}
