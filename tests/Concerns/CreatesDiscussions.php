<?php

namespace Tests\Concerns;

use App\Models\Administrator;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Сущности, под которыми есть обсуждение. Тесты идут в транзакции, поэтому всё создаётся «по-настоящему» и откатывается. */
trait CreatesDiscussions
{
    /** @return list<string> */
    public static function entityKinds(): array
    {
        return ['question', 'post', 'firmware'];
    }

    public static function entityProvider(): array
    {
        return array_combine(self::entityKinds(), array_map(fn ($k) => [$k], self::entityKinds()));
    }

    protected function makeQuestion(User $author, array $overrides = []): Question
    {
        return Question::create($overrides + [
            'title' => 'Тестовый вопрос про насос ' . Str::random(8),
            'description' => 'Машина не сливает воду, подскажите что проверить. ' . Str::random(20),
            'appliance_id' => Appliance::factory()->create()->id,
            'user_id' => $author->id,
        ]);
    }

    /** Страница, под которой можно писать: вопрос автора $owner, существующая статья или прошивка. */
    protected function subject(string $kind, ?User $owner = null): Model
    {
        return match ($kind) {
            'question' => $this->makeQuestion($owner ?? User::factory()->create()),
            'post' => Post::create([
                'title' => 'Тестовая статья ' . Str::random(8), 'description' => 'Описание', 'content' => '<p>Текст статьи</p>',
                'category_id' => Category::factory()->create()->id,
                'administrator_id' => Administrator::forceCreate(['name' => 'Автор', 'email' => Str::random(10) . '@example.invalid', 'password' => 'x'])->id,
            ]),
            'firmware' => Firmware::create(['path_id' => random_int(1_000_000, 9_999_999), 'title' => 'TEST_' . Str::random(8), 'extension' => '.bin', 'size' => 4096]),
        };
    }

    protected function commentOn(Model $subject, User $author, string $text = 'Проверьте реле на плате'): Comment
    {
        return $subject->comments()->create(['user_id' => $author->id, 'text' => $text]);
    }

    protected function pageOf(Model $subject): string
    {
        return match (true) {
            $subject instanceof Question => route('questions.show', ['question' => $subject->slug]),
            $subject instanceof Post => route('articles.show', ['article' => $subject->slug]),
            $subject instanceof Firmware => route('firmwares.show', ['firmware' => $subject->id]),
        };
    }
}
