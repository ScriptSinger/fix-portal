<?php

namespace Tests\Feature;

use App\Jobs\CleanupDeletedUserImages;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Image;
use App\Models\Post;
use App\Models\Question;
use App\Models\User;
use App\Services\ContentImageCleanupService;
use App\Services\DiscussionMessageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageCleanupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_files_survive_nested_commit_and_rollback_and_are_deleted_after_outer_commit(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = "images/users/user_{$user->id}/transaction.jpg";
        Storage::disk('public')->put($path, 'image');
        $image = Image::forceCreate([
            'user_id' => $user->id, 'url' => '/storage/'.$path,
            'mime' => 'image/jpeg', 'size' => 5,
        ]);
        $db = $image->getConnection();
        $db->beginTransaction();
        $db->beginTransaction();
        $image->forceDelete();
        $db->commit();
        Storage::disk('public')->assertExists($path);
        $db->rollBack();
        $this->assertDatabaseHas('images', ['id' => $image->id]);
        Storage::disk('public')->assertExists($path);

        $db->transaction(function () use ($image, $path): void {
            Image::findOrFail($image->id)->forceDelete();
            Storage::disk('public')->assertExists($path);
        });
        Storage::disk('public')->assertMissing($path);
    }

    public function test_avatar_and_content_cleanup_are_cancelled_on_rollback(): void
    {
        Storage::fake('public');
        config(['filesystems.media' => 'public']);
        $user = User::factory()->create();
        $avatarPath = "images/users/user_{$user->id}/avatar.jpg";
        $contentPath = 'galleries/content/rollback.jpg';
        foreach ([$avatarPath, $contentPath] as $path) {
            Storage::disk('public')->put($path, 'image');
        }
        $avatar = $user->avatar()->create(['uri' => $avatarPath, 'mime' => 'image/jpeg', 'size' => 5]);
        $db = $avatar->getConnection();
        $db->beginTransaction();
        $avatar->delete();
        app(ContentImageCleanupService::class)->cleanup(['/storage/'.$contentPath]);
        Storage::disk('public')->assertExists($avatarPath);
        Storage::disk('public')->assertExists($contentPath);
        $db->rollBack();
        // A later successful transaction must not execute discarded callbacks.
        $db->transaction(fn () => $user->update(['name' => 'Updated']));
        Storage::disk('public')->assertExists($avatarPath);
        Storage::disk('public')->assertExists($contentPath);
    }

    public function test_comment_images_are_removed_when_no_longer_referenced(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::query()->create([
            'title' => 'Тестовая статья',
            'slug' => 'test-image-cleanup',
            'administrator_id' => 1,
            'description' => 'Описание',
            'content' => 'Содержимое',
            'category_id' => Category::factory()->create()->id,
            'views' => 0,
        ]);
        $path = "images/users/user_{$user->id}/photo.jpg";
        Storage::disk('public')->put($path, 'image');

        $image = (new Image)->forceFill([
            'user_id' => $user->id,
            'url' => '/storage/'.$path,
            'mime' => 'image/jpeg',
            'size' => 5,
        ]);
        $image->save();
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'text' => '<img src="/storage/'.$path.'" data-id="'.$image->id.'">',
        ]);

        $comment->delete();

        $this->assertDatabaseMissing('images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_telegram_image_is_removed_when_question_is_deleted(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $appliance = Appliance::create(['title' => 'Telegram appliances']);
        $path = 'images/telegram/7/photo.png';
        Storage::disk('public')->put($path, 'image');

        $image = (new Image)->forceFill([
            'user_id' => $user->id,
            'url' => '/storage/'.$path,
            'mime' => 'image/png',
            'size' => 5,
        ]);
        $image->save();
        $question = Question::create([
            'title' => 'Telegram question',
            'description' => '<img src="/storage/'.$path.'" data-id="'.$image->id.'">',
            'appliance_id' => $appliance->id,
            'user_id' => $user->id,
        ]);

        $question->delete();

        $this->assertDatabaseMissing('images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_avatar_file_is_removed_when_user_is_deleted(): void
    {
        DB::table('image_registry_state')->updateOrInsert(['id' => 1], ['indexed_at' => now()]);
        Storage::fake('public');
        $user = User::factory()->create();
        $path = "images/users/user_{$user->id}/avatar.jpg";
        Storage::disk('public')->put($path, 'avatar');
        $user->avatar()->create(['uri' => $path, 'mime' => 'image/jpeg', 'size' => 6]);

        $user->delete();

        (new CleanupDeletedUserImages([], $path))->handle();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_article_content_images_are_removed_when_article_is_deleted(): void
    {
        Storage::fake('public');
        $path = 'galleries/content/article.jpg';
        Storage::disk('public')->put($path, 'image');
        $post = Post::query()->create([
            'title' => 'Статья с изображением',
            'slug' => 'article-with-image',
            'administrator_id' => 1,
            'description' => 'Описание',
            'content' => '<p><img src="/storage/'.$path.'"></p>',
            'category_id' => Category::factory()->create()->id,
            'views' => 0,
        ]);

        $post->delete();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_comment_image_is_removed_when_comment_is_edited(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $post = Post::query()->create([
            'title' => 'Статья для редактирования',
            'slug' => 'article-for-editing',
            'administrator_id' => 1,
            'description' => 'Описание',
            'content' => 'Содержимое',
            'category_id' => Category::factory()->create()->id,
            'views' => 0,
        ]);
        $path = "images/users/user_{$user->id}/old.jpg";
        Storage::disk('public')->put($path, 'image');
        $image = (new Image)->forceFill([
            'user_id' => $user->id,
            'url' => '/storage/'.$path,
            'mime' => 'image/jpeg',
            'size' => 5,
        ]);
        $image->save();
        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'text' => '<img src="/storage/'.$path.'" data-id="'.$image->id.'">',
        ]);

        app(DiscussionMessageService::class)->update($comment, ['text' => 'Новый текст'], $user->id);

        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }

    public function test_article_content_image_is_removed_when_article_is_edited(): void
    {
        Storage::fake('public');
        $path = 'galleries/content/old-article.jpg';
        Storage::disk('public')->put($path, 'image');
        $post = Post::query()->create([
            'title' => 'Статья для обновления',
            'slug' => 'article-content-editing',
            'administrator_id' => 1,
            'description' => 'Описание',
            'content' => '<p><img src="/storage/'.$path.'"></p>',
            'category_id' => Category::factory()->create()->id,
            'views' => 0,
        ]);

        $post->update(['content' => '<p>Новый текст без изображения</p>']);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_orphan_image_command_removes_only_old_unreferenced_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $oldPath = "images/users/user_{$user->id}/orphan.jpg";
        Storage::disk('public')->put($oldPath, 'image');
        $old = (new Image)->forceFill([
            'user_id' => $user->id,
            'url' => '/storage/'.$oldPath,
            'mime' => 'image/jpeg',
            'size' => 5,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        $old->save();

        $newPath = "images/users/user_{$user->id}/new.jpg";
        Storage::disk('public')->put($newPath, 'image');
        $new = (new Image)->forceFill([
            'user_id' => $user->id,
            'url' => '/storage/'.$newPath,
            'mime' => 'image/jpeg',
            'size' => 5,
        ]);
        $new->save();

        $this->artisan('images:prune-orphans', ['--days' => '1', '--apply' => true])
            ->assertExitCode(0)
            ->expectsOutput('Удалено брошенных изображений: 1');

        $this->assertDatabaseMissing('images', ['id' => $old->id]);
        $this->assertDatabaseHas('images', ['id' => $new->id]);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }
}
