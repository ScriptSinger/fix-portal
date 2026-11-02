<?php

declare(strict_types=1);

namespace App\Http\Controllers\MoonShine;

use App\Http\Controllers\Controller;
use App\Models\TelegramQuestionMessage;
use App\Services\TelegramBotApi;
use Illuminate\Http\Response;

class TelegramQuestionPhotoController extends Controller
{
    public function __invoke(
        TelegramQuestionMessage $telegramQuestionMessage,
        int $photo,
        TelegramBotApi $telegram,
    ): Response {
        $fileId = $telegramQuestionMessage->telegramPhotoFileIds()[$photo] ?? null;
        abort_if($fileId === null, 404);

        $file = $telegram->call('getFile', ['file_id' => $fileId]);
        $filePath = (string) ($file['file_path'] ?? '');
        abort_if($filePath === '', 404);

        $contents = $telegram->downloadFile($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
            'Content-Disposition' => 'inline',
        ]);
    }
}
