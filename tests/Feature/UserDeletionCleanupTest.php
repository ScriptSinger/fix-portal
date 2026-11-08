<?php

namespace Tests\Feature;

use App\Jobs\CleanupDeletedUserImages;
use App\Models\Image;
use App\Models\User;
use App\MoonShine\Resources\UserResource;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

class UserDeletionCleanupTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['filesystems.media' => 'public']);
        DB::table('image_registry_state')->updateOrInsert(['id' => 1], ['indexed_at' => now()]);
    }

    public function test_cascade_collects_other_authors_replies_and_preserves_shared_files(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $question = $this->makeQuestion($user);
        $comment = $this->commentOn($question, $user);
        $path = 'images/users/user_'.$other->id.'/cascade.jpg';
        Storage::disk('public')->put($path, 'image');
        $image = Image::forceCreate(['url' => '/storage/'.$path, 'user_id' => $other->id, 'mime' => 'image/jpeg', 'size' => 5]);
        $html = '<img src="'.$image->url.'">';
        $reply = $comment->replies()->create(['user_id' => $other->id, 'text' => $html]);
        $survivor = $this->commentOn($this->makeQuestion($other), $other, $html);
        $before = DB::table('jobs')->count();
        $user->delete();
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseMissing('imageables', ['imageable_type' => $reply->getMorphClass(), 'imageable_id' => $reply->id]);
        $this->assertSame($before + 1, DB::table('jobs')->count());
        Storage::disk('public')->assertExists($path);
        (new CleanupDeletedUserImages([$image->id], null))->handle();
        Storage::disk('public')->assertExists($path);
        $survivor->delete();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_rollback_restores_user_and_discards_cleanup_job(): void
    {
        $user = User::factory()->create();
        $before = DB::table('jobs')->count();
        DB::beginTransaction();
        $user->delete();
        $this->assertSame($before + 1, DB::table('jobs')->count());
        DB::rollBack();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertSame($before, DB::table('jobs')->count());
    }

    public function test_job_deletes_unreferenced_image_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $path = 'images/users/user_'.$user->id.'/orphan.jpg';
        Storage::disk('public')->put($path, 'image');
        $image = Image::forceCreate(['url' => '/storage/'.$path, 'user_id' => $user->id, 'mime' => 'image/jpeg', 'size' => 5]);
        $job = new CleanupDeletedUserImages([$image->id], null);
        $job->handle();
        $job->handle();
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
    }

    public function test_job_preserves_record_and_throws_when_storage_delete_fails(): void
    {
        $user = User::factory()->create();
        $path = 'images/users/user_'.$user->id.'/failure.jpg';
        $image = Image::forceCreate(['url' => '/storage/'.$path, 'user_id' => $user->id, 'mime' => 'image/jpeg', 'size' => 5]);
        $disk = \Mockery::mock(Filesystem::class);
        $disk->shouldReceive('exists')->once()->with($path)->andReturnTrue();
        $disk->shouldReceive('delete')->once()->with($path)->andReturnFalse();
        Storage::shouldReceive('disk')->once()->with('public')->andReturn($disk);
        try {
            (new CleanupDeletedUserImages([$image->id], null))->handle();
            $this->fail('Storage failure must trigger a retry.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Unable to delete', $e->getMessage());
        }
        $this->assertDatabaseHas('images', ['id' => $image->id]);
    }

    public function test_pruning_unverified_users_queues_cleanup(): void
    {
        $user = User::factory()->unverified()->create(['created_at' => now()->subDays(10)]);
        $before = DB::table('jobs')->count();
        $this->artisan('users:prune-unverified', ['--days' => 7, '--apply' => true])->assertSuccessful();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertSame($before + 1, DB::table('jobs')->count());
    }

    public function test_moonshine_mass_delete_uses_the_same_cleanup_path(): void
    {
        $users = User::factory()->count(2)->create();
        $before = DB::table('jobs')->count();
        app(UserResource::class)->massDelete($users->modelKeys());
        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
        $this->assertSame($before + 2, DB::table('jobs')->count());
    }
}
