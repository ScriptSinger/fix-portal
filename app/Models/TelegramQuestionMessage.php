<?php

namespace App\Models;

use App\Enums\TelegramQuestionResolutionStatus;
use App\Telegram\TelegramAlbum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'resolution_status',
        'resolved_at',
        'solution_telegram_user_id',
        'solution_telegram_message_id',
        'question_id',
        'appliance_id',
        'moderation_chat_id',
        'moderation_message_id',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'resolution_status' => TelegramQuestionResolutionStatus::class,
        'resolved_at' => 'datetime',
    ];

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

            $albums = (array) data_get($message, 'album_photos', []);
            $mediaGroupId = (string) data_get($message, 'media_group_id', '');
            if ($mediaGroupId !== '') {
                $message['chat'] ??= ['id' => $this->telegram_chat_id];
                $albums = array_merge($albums, app(TelegramAlbum::class)->collect($message, $mediaGroupId));
            }
            foreach ($albums as $album) {
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

    public function publication(): HasOne
    {
        return $this->hasOne(TelegramQuestionPublication::class, 'telegram_question_message_id');
    }
}
