<?php

namespace App\Services;

use App\Models\Image;
use App\Telegram\TelegramAlbum;
use Illuminate\Support\Facades\Storage;

class TelegramPhotoImporter
{
    public function __construct(private readonly TelegramBotApi $telegram) {}

    /**
     * Download and stage files before opening a database transaction. Content-addressed
     * files may remain after a failed/racing decision; retries reuse the same paths.
     * No image rows are created until the publication transaction accepts the decision.
     *
     * @return list<array{url: string, mime: string, size: int, user_id: int}>
     */
    public function prepare(int $queueId, array $payload): array
    {
        $messages = [$payload['message'] ?? [], ...($payload['attached_messages'] ?? [])];
        $files = [];
        foreach ($messages as $message) {
            $groups = [$message['photo'] ?? [], ...($message['album_photos'] ?? [])];
            $mediaGroupId = (string) ($message['media_group_id'] ?? '');
            if ($mediaGroupId !== '' && isset($message['chat']['id'])) {
                $groups = array_merge($groups, app(TelegramAlbum::class)->collect($message, $mediaGroupId));
            }
            foreach ($groups as $photos) {
                if (! is_array($photos) || $photos === []) {
                    continue;
                }
                $fileId = (string) data_get(end($photos), 'file_id');
                if ($fileId !== '' && ! isset($files[$fileId])) {
                    $files[$fileId] = $this->download($queueId, $fileId);
                }
            }
        }

        return array_values($files);
    }

    /** Persist prepared metadata inside the question transaction, without network I/O. */
    public function persist(array $files): array
    {
        return array_map(static function (array $file): array {
            $image = new Image;
            $image->forceFill($file)->save();

            return ['id' => (int) $image->id, 'url' => $image->url];
        }, $files);
    }

    private function download(int $queueId, string $fileId): array
    {
        $file = $this->telegram->call('getFile', ['file_id' => $fileId]);
        $filePath = (string) ($file['file_path'] ?? '');
        if ($filePath === '') {
            throw new \RuntimeException('Telegram photo path is missing.');
        }
        $contents = $this->telegram->downloadFile($filePath);
        if ($contents === '' || strlen($contents) > 10 * 1024 * 1024) {
            throw new \RuntimeException('Telegram photo is empty or too large.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: '';
        if (! str_starts_with($mime, 'image/')) {
            throw new \RuntimeException('Telegram file is not an image.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) ?: 'jpg';
        $path = 'images/telegram/'.$queueId.'/'.hash('sha256', $contents).'.'.$extension;
        $disk = Storage::disk('public');
        if (! $disk->put($path, $contents)) {
            throw new \RuntimeException('Telegram photo could not be stored.');
        }

        return [
            'url' => $disk->url($path),
            'mime' => $mime,
            'size' => strlen($contents),
            'user_id' => (int) config('services.telegram.author_user_id'),
        ];
    }
}
