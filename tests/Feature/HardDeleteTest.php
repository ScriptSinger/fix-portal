<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\BlockedUserAgent;
use App\Models\Blocker;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Firmware;
use App\Models\FirmwareRedirect;
use App\Models\Post;
use App\Models\Question;
use App\Models\Reaction;
use App\Models\Reply;
use App\Models\SocialAccount;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

/** Корзины нет: удалённое стирается окончательно вместе с тем, что от него зависело. */
class HardDeleteTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    private function reply(Comment $comment, User $author): Reply
    {
        return Reply::forceCreate(['user_id' => $author->id, 'comment_id' => $comment->id, 'text' => 'Согласен, плата']);
    }

    private function react(Comment|Reply $target, User $by): Reaction
    {
        return Reaction::forceCreate(['user_id' => $by->id, 'reactable_type' => $target::class, 'reactable_id' => $target->id, 'emoji' => '👍']);
    }

    public function test_no_model_uses_soft_deletes(): void
    {
        foreach ([User::class, Question::class, Comment::class, Reply::class, Post::class, Firmware::class, Tag::class, Category::class, Appliance::class, Blocker::class, BlockedUserAgent::class] as $model) {
            $this->assertNotContains(SoftDeletes::class, class_uses_recursive($model), "$model всё ещё использует SoftDeletes");
        }
    }

    public function test_deleting_a_question_erases_its_discussion(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $question = $this->makeQuestion($author);
        $comment = $this->commentOn($question, $other);
        $reply = $this->reply($comment, $author);
        $commentReaction = $this->react($comment, $author);
        $replyReaction = $this->react($reply, $other);

        $question->delete();

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseMissing('reactions', ['id' => $commentReaction->id]);
        $this->assertDatabaseMissing('reactions', ['id' => $replyReaction->id]);
    }

    public function test_deleting_a_comment_keeps_the_question_and_other_comments(): void
    {
        $author = User::factory()->create();
        $question = $this->makeQuestion($author);
        $gone = $this->commentOn($question, $author, 'Удаляемый');
        $stays = $this->commentOn($question, $author, 'Остаётся');
        $reply = $this->reply($gone, $author);

        $gone->delete();

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseHas('comments', ['id' => $stays->id]);
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    public function test_deleting_a_user_erases_their_content_and_social_links_but_not_others(): void
    {
        $gone = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->makeQuestion($gone);
        $theirs = $this->makeQuestion($other);
        $commentOnTheirs = $this->commentOn($theirs, $gone);
        $otherComment = $this->commentOn($theirs, $other);
        SocialAccount::create(['user_id' => $gone->id, 'provider' => 'yandex', 'provider_user_id' => 'gone-y']);
        $reaction = $this->react($otherComment, $gone);

        $gone->delete();

        $this->assertDatabaseMissing('users', ['id' => $gone->id]);
        $this->assertDatabaseMissing('questions', ['id' => $mine->id]);
        $this->assertDatabaseMissing('comments', ['id' => $commentOnTheirs->id]);
        $this->assertDatabaseMissing('social_accounts', ['provider_user_id' => 'gone-y']);
        $this->assertDatabaseMissing('reactions', ['id' => $reaction->id]);
        $this->assertDatabaseHas('questions', ['id' => $theirs->id]);
        $this->assertDatabaseHas('comments', ['id' => $otherComment->id]);
    }

    public function test_merging_duplicates_redirects_their_addresses_and_erases_them(): void
    {
        $hash = md5(uniqid('', true));
        $make = fn (int $pathId, int $views) => Firmware::create(['path_id' => $pathId, 'title' => 'MERGE_' . $pathId, 'extension' => '.bin', 'size' => 1, 'crc32' => $hash, 'views_count' => $views]);
        $main = $make(7000001, 5);
        $dup = $make(7000002, 3);
        $dup2 = $make(7000003, 2);
        $comment = $this->commentOn($dup, User::factory()->create());

        $this->artisan('firmware:merge-duplicates', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseHas('firmware', ['id' => $main->id, 'views_count' => 10]);
        $this->assertDatabaseMissing('firmware', ['id' => $dup->id]);
        $this->assertDatabaseMissing('firmware', ['id' => $dup2->id]);
        $this->assertSame($main->id, (int) FirmwareRedirect::find($dup->id)->new_id);
        $this->assertSame($main->id, (int) FirmwareRedirect::find($dup2->id)->new_id);
        $this->assertSame($main->id, (int) $comment->refresh()->commentable_id);
    }

    public function test_a_second_merge_repoints_old_redirects_to_the_new_main_record(): void
    {
        $hash = md5(uniqid('', true));
        $first = Firmware::create(['path_id' => 7000011, 'title' => 'A', 'extension' => '.bin', 'size' => 1, 'crc32' => $hash]);
        $second = Firmware::create(['path_id' => 7000012, 'title' => 'B', 'extension' => '.bin', 'size' => 1, 'crc32' => $hash]);
        // раньше второй уже был основной записью для третьего адреса
        FirmwareRedirect::create(['old_id' => 7777777, 'new_id' => $second->id]);

        $this->artisan('firmware:merge-duplicates', ['--apply' => true])->assertSuccessful();

        $this->assertSame($first->id, (int) FirmwareRedirect::find($second->id)->new_id);
        $this->assertSame($first->id, (int) FirmwareRedirect::find(7777777)->new_id);
    }
}
