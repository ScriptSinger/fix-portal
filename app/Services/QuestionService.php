<?php

namespace App\Services;

use App\Models\Question;
use App\Models\User;
use App\Services\Announcements\QuestionAnnouncer;
use App\Support\ContentImages;
use App\Support\MessageBody;

/** Сохранение вопроса: текст экранируется, к нему добавляются только собственные фото автора. */
class QuestionService
{
    public function __construct(private readonly QuestionAnnouncer $announcer)
    {
    }

    public function create(User $author, array $data): Question
    {
        $question = Question::create($this->fields($author, $data));

        // вопрос уходит в чат мастеров; без настроенных каналов ничего не происходит
        $this->announcer->announce($question);

        return $question;
    }

    public function update(Question $question, User $editor, array $data): Question
    {
        // фото, уже стоявшие в вопросе, остаются, даже если их загрузил не редактор
        $kept = array_column(ContentImages::extract($question->description)[1], 'full');
        $question->update($this->fields($editor, $data, $kept));

        // в чате показаны заголовок, техника, модель, код ошибки и начало описания: если они изменились, сообщение обновляется
        if ($question->wasChanged(['title', 'description', 'model', 'error_code', 'appliance_id'])) {
            $this->announcer->refresh($question);
        }

        return $question;
    }

    /** @param list<string> $keep */
    private function fields(User $user, array $data, array $keep = []): array
    {
        $data['description'] = MessageBody::make($data['description'] ?? null, $data['images'] ?? [], $user->id, $keep);
        $data['user_id'] = $user->id;
        unset($data['images']);

        return $data;
    }
}
