<?php

namespace Tests\Feature;

use App\Models\Reaction;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Обсуждение одинаково под вопросами, статьями и прошивками: лента, комментарии, ответы, реакции и уведомления. */
class DiscussionTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    #[DataProvider('entityProvider')]
    public function test_every_page_shows_the_same_feed(string $kind): void
    {
        $subject = $this->subject($kind);
        $this->commentOn($subject, User::factory()->create(), 'Тестовый текст обсуждения');

        $response = $this->get($this->pageOf($subject))->assertOk()->assertSee('id="comments"', false)->assertSee('Тестовый текст обсуждения');
        $response->assertSee($kind === 'question' ? 'Ответы ·' : 'Комментарии ·', false);
    }

    #[DataProvider('entityProvider')]
    public function test_comment_is_stored_escaped(string $kind): void
    {
        $subject = $this->subject($kind);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', ['type' => $kind, 'id' => $subject->id]), ['text' => 'Ответ <b>жирным</b>'])->assertRedirect();

        $comment = $subject->comments()->latest('id')->first();
        $this->assertSame('Ответ &lt;b&gt;жирным&lt;/b&gt;', $comment->text);
        $this->assertSame($user->id, $comment->user_id);
    }

    #[DataProvider('entityProvider')]
    public function test_after_sending_the_user_lands_on_the_new_message(string $kind): void
    {
        $subject = $this->subject($kind);
        $page = $this->pageOf($subject);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from($page)->post(route('comments.store', ['type' => $kind, 'id' => $subject->id]), ['text' => 'Новое сообщение']);
        $comment = $subject->comments()->latest('id')->first();
        $response->assertRedirect($page . '#c-' . $comment->id);

        $reply = $this->actingAs(User::factory()->create())->from($page)->post(route('comments.replies.store', ['id' => $comment->id]), ['text' => 'Ответ']);
        $reply->assertRedirect($page . '#r-' . Reply::latest('id')->value('id'));
    }

    public function test_empty_comment_is_rejected_and_guests_cannot_comment(): void
    {
        $subject = $this->subject('question');
        $url = route('comments.store', ['type' => 'question', 'id' => $subject->id]);

        $this->actingAs(User::factory()->create())->post($url, ['text' => ''])->assertSessionHasErrors('text');
        auth()->logout();
        $this->post($url, ['text' => 'гость'])->assertRedirect(route('login'));
        $this->assertSame(0, $subject->comments()->count());
    }

    #[DataProvider('entityProvider')]
    public function test_reply_notifies_the_comment_owner_with_the_place(string $kind): void
    {
        $subject = $this->subject($kind);
        [$owner, $other] = [User::factory()->create(), User::factory()->create()];
        $comment = $this->commentOn($subject, $owner);

        $this->actingAs($other)->post(route('comments.replies.store', ['id' => $comment->id]), ['text' => 'Согласен'])->assertRedirect();

        $note = $owner->notifications()->first();
        $this->assertNotNull($note);
        $this->assertSame('reply', $note->data['kind']);
        $this->assertSame(['question' => 'вопросе', 'post' => 'статье', 'firmware' => 'прошивке'][$kind], $note->data['in']);
        $this->assertStringContainsString('#r-' . Reply::latest('id')->value('id'), $note->data['url']);
    }

    public function test_replying_to_yourself_does_not_notify(): void
    {
        $subject = $this->subject('question');
        $owner = User::factory()->create();
        $comment = $this->commentOn($subject, $owner);

        $this->actingAs($owner)->post(route('comments.replies.store', ['id' => $comment->id]), ['text' => 'дополню']);

        $this->assertSame(0, $owner->notifications()->count());
    }

    public function test_question_author_is_notified_about_answers_but_not_about_own(): void
    {
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);

        $this->actingAs(User::factory()->create())->post(route('comments.store', ['type' => 'question', 'id' => $question->id]), ['text' => 'Попробуйте промыть фильтр']);
        $this->assertSame('answer', $author->notifications()->first()->data['kind']);

        $this->actingAs($author)->post(route('comments.store', ['type' => 'question', 'id' => $question->id]), ['text' => 'Спасибо, попробую']);
        $this->assertSame(1, $author->notifications()->count());
    }

    public function test_reaction_is_set_switched_and_removed(): void
    {
        $subject = $this->subject('post');
        $owner = User::factory()->create();
        $comment = $this->commentOn($subject, $owner);
        $me = User::factory()->create();
        $url = route('reactions.toggle', ['type' => 'comment', 'id' => $comment->id]);

        $this->actingAs($me)->postJson($url, ['emoji' => '🔥'])->assertOk()->assertJson(['mine' => '🔥', 'counts' => ['🔥' => 1]]);
        $this->postJson($url, ['emoji' => '👍'])->assertOk()->assertJson(['mine' => '👍', 'counts' => ['👍' => 1]]);
        $this->postJson($url, ['emoji' => '👍'])->assertOk()->assertJson(['mine' => null, 'counts' => []]);
        $this->assertSame(0, Reaction::where('reactable_id', $comment->id)->count());
    }

    public function test_reaction_counts_keep_different_emoji_apart(): void
    {
        $comment = $this->commentOn($this->subject('post'), User::factory()->create());
        $url = route('reactions.toggle', ['type' => 'comment', 'id' => $comment->id]);

        $this->actingAs(User::factory()->create())->postJson($url, ['emoji' => '👍']);
        $response = $this->actingAs(User::factory()->create())->postJson($url, ['emoji' => '🔥'])->assertOk();

        // MySQL с коллацией utf8mb4_unicode_ci считает разные эмодзи равными, при подсчёте в SQL они склеились бы
        $this->assertSame(['👍' => 1, '🔥' => 1], $response->json('counts'));
    }

    public function test_reaction_rejects_unknown_emoji_and_guests(): void
    {
        $comment = $this->commentOn($this->subject('post'), User::factory()->create());
        $url = route('reactions.toggle', ['type' => 'comment', 'id' => $comment->id]);

        $this->actingAs(User::factory()->create())->postJson($url, ['emoji' => '💩'])->assertStatus(422);
        auth()->logout();
        $this->postJson($url, ['emoji' => '🔥'])->assertStatus(401);
    }

    public function test_the_same_reaction_notifies_only_once(): void
    {
        $owner = User::factory()->create();
        $comment = $this->commentOn($this->subject('question'), $owner);
        $url = route('reactions.toggle', ['type' => 'comment', 'id' => $comment->id]);

        $this->actingAs(User::factory()->create());
        foreach (range(1, 3) as $i) {
            $this->postJson($url, ['emoji' => '🔥']);
        }

        $this->assertSame(1, $owner->notifications()->where('data->kind', 'reaction')->count());
    }

    public function test_html_in_comment_text_never_reaches_the_page_unescaped(): void
    {
        $subject = $this->subject('question');
        $this->actingAs(User::factory()->create())->post(route('comments.store', ['type' => 'question', 'id' => $subject->id]), ['text' => '<script>alert(1)</script>']);

        $this->get($this->pageOf($subject))->assertDontSee('<script>alert(1)</script>', false);
    }
}
