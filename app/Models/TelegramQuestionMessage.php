<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramQuestionMessage extends Model
{
    protected $fillable = [
        'telegram_chat_id',
        'telegram_message_id',
        'telegram_update_id',
        'telegram_user_id',
        'text',
        'payload',
        'status',
        'question_id',
        'appliance_id',
        'moderation_chat_id',
        'moderation_message_id',
        'error_message',
    ];

    protected $casts = ['payload' => 'array'];

    /**
     * Return Telegram file IDs for all photos attached to this moderation item.
     *
     * @return list<string>
     */
    public function telegramPhotoFileIds(): array
    {
        $payload = is_array($this->payload) ? $this->payload : [];
        $messages = [];

        if (is_array(data_get($payload, 'message'))) {
            $messages[] = data_get($payload, 'message');
        }

        foreach ((array) data_get($payload, 'attached_messages', []) as $message) {
            if (is_array($message)) {
                $messages[] = $message;
            }
        }

        $fileIds = [];
        foreach ($messages as $message) {
            $photo = data_get($message, 'photo');
            $fileId = (string) data_get(is_array($photo) ? end($photo) : null, 'file_id');
            if ($fileId !== '') {
                $fileIds[] = $fileId;
            }

            foreach ((array) data_get($message, 'album_photos', []) as $album) {
                $photo = is_array($album) ? end($album) : null;
                $fileId = (string) data_get($photo, 'file_id');
                if ($fileId !== '') {
                    $fileIds[] = $fileId;
                }
            }
        }

        return array_values(array_unique($fileIds));
    }

    public function appliance(): BelongsTo
    {
        return $this->belongsTo(Appliance::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
