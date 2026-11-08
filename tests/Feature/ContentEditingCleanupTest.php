<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\QuestionResource;
use App\MoonShine\Resources\ReplyResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

class ContentEditingCleanupTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    public static function editableBodies(): array
    {
        return [['question', 'description'], ['comment', 'text'], ['reply', 'text']];
    }

    #[DataProvider('editableBodies')]
    public function test_model_save_cleans_removed_images_only_after_commit_and_preserves_shared_images(string $kind, string $field): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = "images/users/user_{$user->id}/editing.jpg";
        Storage::disk('public')->put($path, 'image');
        $image = Image::forceCreate(['user_id' => $user->id, 'url' => '/storage/'.$path, 'mime' => 'image/jpeg', 'size' => 5]);
        $html = '<img src="'.$image->url.'" data-id="'.$image->id.'">';
        $question = $this->makeQuestion($user);
        $comment = $this->commentOn($question, $user);
        $model = match ($kind) {
            'question' => $question,
            'comment' => $comment,
            'reply' => $comment->replies()->create(['user_id' => $user->id, 'text' => 'Reply']),
        };
        $model->update([$field => $html]);
        $model->update([$field => 'Changed text '.$html]);
        Storage::disk('public')->assertExists($path);

        $db = $model->getConnection();
        $db->beginTransaction();
        $model->update([$field => 'No image']);
        Storage::disk('public')->assertExists($path);
        $db->rollBack();
        $model->refresh();
        $db->transaction(fn () => $model->update([$field => 'Still here '.$html]));
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('images', ['id' => $image->id]);

        $other = $this->commentOn($this->subject('post'), $user, $html);
        $db->transaction(function () use ($model, $field, $path): void {
            $model->update([$field => 'No image']);
            Storage::disk('public')->assertExists($path);
        });
        Storage::disk('public')->assertExists($path);
        $db->transaction(function () use ($other, $path): void {
            $other->update(['text' => 'No image either']);
            Storage::disk('public')->assertExists($path);
        });
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }

    public function test_question_editor_images_are_shared_with_articles_and_cleaned_after_last_reference(): void
    {
        Storage::fake('public');
        config(['filesystems.media' => 'public']);
        $path = 'galleries/content/shared-question.jpg';
        Storage::disk('public')->put($path, 'image');
        $html = '<img src="/storage/'.$path.'">';
        $question = $this->makeQuestion(User::factory()->create(), ['description' => $html]);
        $post = $this->subject('post');
        $post->update(['content' => $html]);
        $post->update(['content' => 'No image']);
        Storage::disk('public')->assertExists($path);
        $question->update(['description' => 'No image']);
        Storage::disk('public')->assertMissing($path);
    }

    #[DataProvider('editableBodies')]
    public function test_moonshine_update_removes_unused_image(string $kind, string $field): void
    {
        Storage::fake('public');
        $role = MoonshineUserRole::first() ?? MoonshineUserRole::forceCreate(['name' => 'Admin']);
        $admin = MoonshineUser::forceCreate([
            'moonshine_user_role_id' => $role->id, 'name' => 'Admin',
            'email' => uniqid('editor').'@example.invalid', 'password' => bcrypt('test-password'),
        ]);
        $user = User::factory()->create();
        $path = "images/users/user_{$user->id}/admin-edit.jpg";
        Storage::disk('public')->put($path, 'image');
        $image = Image::forceCreate(['user_id' => $user->id, 'url' => '/storage/'.$path, 'mime' => 'image/jpeg', 'size' => 5]);
        $html = '<img src="'.$image->url.'" data-id="'.$image->id.'">';
        $question = $this->makeQuestion($user);
        [$model, $resource] = match ($kind) {
            'question' => [$question, QuestionResource::class],
            'comment' => [$this->commentOn($question, $user), CommentResource::class],
            'reply' => [$this->commentOn($question, $user)->replies()->create(['text' => 'Reply', 'user_id' => $user->id]), ReplyResource::class],
        };
        $model->update([$field => $html]);
        $data = [$field => 'Edited in MoonShine'];
        if ($kind === 'question') {
            $data += ['title' => $question->title, 'appliance_id' => $question->appliance_id, 'user_id' => $user->id];
        }
        $this->actingAs($admin, 'moonshine')->putJson(route('moonshine.crud.update', [
            'resourceUri' => app($resource)->getUriKey(), 'resourceItem' => $model->id,
        ]), $data)->assertSuccessful();

        $this->assertSame('Edited in MoonShine', $model->fresh()->getAttribute($field));
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }
}
