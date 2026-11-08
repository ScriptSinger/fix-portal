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
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true), 415);

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
