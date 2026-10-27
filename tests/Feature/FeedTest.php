<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Discussion\Feed;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Лента обсуждения как данные: серии сообщений, цитаты, решение, права. Шаблон только рисует то, что здесь посчитано. */
class FeedTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function feed($subject, ?User $viewer = null): Feed
    {
        return Feed::for($subject->load(['comments.user', 'comments.replies.user']), $viewer?->id);
    }

    public function test_messages_of_one_person_in_a_row_form_a_series(): void
    {
        [$a, $b] = [User::factory()->create(), User::factory()->create()];
        $question = $this->makeQuestion(User::factory()->create());
        $this->commentOn($question, $a, 'раз');
        $this->commentOn($question, $a, 'два');
        $this->commentOn($question, $b, 'три');

        $entries = $this->feed($question)->entries()->values();

        $this->assertSame([true, false, true], $entries->map->firstInSeries->all());
        $this->assertSame([false, true, true], $entries->map->lastInSeries->all());
        $this->assertSame(3, $this->feed($question)->total());
        $this->assertSame(2, $this->feed($question)->people());
    }

    public function test_reply_quotes_the_comment_or_the_exact_reply(): void
    {
        [$a, $b] = [User::factory()->create(['name' => 'Анна']), User::factory()->create(['name' => 'Борис'])];
        $question = $this->makeQuestion(User::factory()->create());
        $comment = $this->commentOn($question, $a, 'Исходный комментарий');
        $first = $comment->replies()->create(['user_id' => $b->id, 'text' => 'Первый ответ']);
        $comment->replies()->create(['user_id' => $a->id, 'text' => 'Ответ на ответ', 'quote_reply_id' => $first->id]);

        $entries = $this->feed($question)->entries()->values();

        $this->assertNull($entries[0]->quote);
        $this->assertSame(['c-' . $comment->id, 'Анна'], [$entries[1]->quote->anchor, $entries[1]->quote->authorName]);
        $this->assertSame(['r-' . $first->id, 'Борис'], [$entries[2]->quote->anchor, $entries[2]->quote->authorName]);
        $this->assertSame($comment->id, $entries[2]->rootId);
    }

    public function test_photos_are_taken_out_of_the_text(): void
    {
        $question = $this->makeQuestion(User::factory()->create());
        $this->commentOn($question, User::factory()->create(), 'Смотрите<img src="/storage/images/users/user_1/x.jpg">');

        $entry = $this->feed($question)->entries()->first();

        $this->assertSame('Смотрите', $entry->body);
        $this->assertSame(['/storage/images/users/user_1/x.jpg'], array_column($entry->photos, 'full'));
    }

    public function test_solution_flags_and_who_may_mark(): void
    {
        [$author, $helper] = [User::factory()->create(), User::factory()->create()];
        $question = $this->makeQuestion($author);
        $answer = $this->commentOn($question, $helper);
        $own = $this->commentOn($question, $author);

        $asAuthor = $this->feed($question, $author)->entries()->values();
        $this->assertSame([true, false], $asAuthor->map->canMarkSolution->all());
        $this->assertSame([false, true], $asAuthor->map->byQuestionAuthor->all());
        $this->assertSame([false, false], $this->feed($question, $helper)->entries()->map->canMarkSolution->values()->all());

        $question->forceFill(['solved_at' => now(), 'solution_type' => 'comment', 'solution_id' => $answer->id])->save();
        $feed = $this->feed($question->refresh(), $author);

        $this->assertTrue($feed->entries()->first()->isSolution);
        $this->assertFalse($feed->entries()->first()->canMarkSolution);
        $this->assertSame('c-' . $answer->id, $feed->solution()['anchor']);
        $this->assertSame(1, $feed->entries()->first()->solutions);
    }

    public function test_articles_and_firmwares_have_comment_wording_and_no_question_features(): void
    {
        foreach (['post', 'firmware'] as $kind) {
            $subject = $this->subject($kind);
            $this->commentOn($subject, User::factory()->create());
            $feed = $this->feed($subject, User::factory()->create());

            $this->assertSame('Комментарии', $feed->words()['title']);
            $this->assertNull($feed->solution());
            $this->assertFalse($feed->entries()->first()->canMarkSolution);
            $this->assertFalse($feed->entries()->first()->byQuestionAuthor);
        }
        $this->assertSame('Ответы', $this->feed($this->makeQuestion(User::factory()->create()))->words()['title']);
    }
}
