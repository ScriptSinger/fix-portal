<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\Services\ImageRegistry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesDiscussions;
use Tests\TestCase;

class ImageRegistryTest extends TestCase
{
    use CreatesDiscussions, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['filesystems.media' => 'public']);
        DB::table('image_registry_state')->insert(['id' => 1, 'indexed_at' => now()]);
    }

    public function test_shared_image_is_preserved_until_last_field_is_removed_without_html_search(): void
    {
        $path = 'galleries/content/shared.jpg';
        Storage::disk('public')->put($path, 'image');
        $image = app(ImageRegistry::class)->register('public', $path);
        $html = '<img src="'.$image->url.'">';
        $post = $this->subject('post');
        $post->update(['content' => $html, 'description' => $html]);
        $question = $this->makeQuestion(User::factory()->create(), ['description' => $html]);
        $this->assertSame(3, DB::table('imageables')->where('image_id', $image->id)->count());
        DB::enableQueryLog();
        $post->update(['content' => 'No image']);
        Storage::disk('public')->assertExists($path);
        $post->update(['description' => 'No image']);
        Storage::disk('public')->assertExists($path);
        $question->delete();
        Storage::disk('public')->assertMissing($path);
        foreach (DB::getQueryLog() as $query) {
            $this->assertStringNotContainsString(' like ', strtolower($query['query']));
        }
        DB::disableQueryLog();
    }

    public function test_rollback_restores_usage_and_keeps_file(): void
    {
        $path = 'galleries/content/rollback.jpg';
        Storage::disk('public')->put($path, 'image');
        $image = app(ImageRegistry::class)->register('public', $path);
        $question = $this->makeQuestion(User::factory()->create(), ['description' => '<img src="'.$image->url.'">']);
        DB::beginTransaction();
        $question->update(['description' => 'Removed']);
        $this->assertSame(0, DB::table('imageables')->where('image_id', $image->id)->count());
        DB::rollBack();
        $this->assertSame(1, DB::table('imageables')->where('image_id', $image->id)->count());
        Storage::disk('public')->assertExists($path);
    }

    public function test_index_is_repeatable_and_covers_abandoned_editor_uploads(): void
    {
        DB::table('image_registry_state')->delete();
        Storage::disk('public')->put('galleries/content/abandoned.jpg', 'image');
        $post = $this->subject('post');
        Storage::disk('public')->put('galleries/content/legacy.jpg', 'image');
        DB::table('posts')->where('id', $post->id)->update(['content' => '<img src="/storage/galleries/content/legacy.jpg">']);
        $this->artisan('images:index', ['--activate' => true])->assertSuccessful();
        $this->artisan('images:index', ['--activate' => true])->assertSuccessful();
        $this->assertTrue(app(ImageRegistry::class)->ready());
        $this->assertSame(2, Image::where('path', 'like', 'galleries/content/%')->count());
        $this->assertSame(1, DB::table('imageables')->count());
        $this->travel(2)->days();
        $this->artisan('images:prune-orphans', ['--apply' => true])->assertSuccessful();
        Storage::disk('public')->assertMissing('galleries/content/abandoned.jpg');
        Storage::disk('public')->assertExists('galleries/content/legacy.jpg');
    }

    public function test_foreign_host_and_traversal_are_not_registered_as_owned_files(): void
    {
        $registry = app(ImageRegistry::class);
        $this->assertNull($registry->location('https://foreign.invalid/storage/galleries/content/test.jpg'));
        $this->assertNull($registry->location('/storage/galleries/content/../secret'));
        $this->assertNull($registry->location('/storage/galleries/content/%2e%2e/secret'));
    }

    public function test_quiet_save_of_partial_model_preserves_other_fields(): void
    {
        Storage::disk('public')->put('galleries/content/description.jpg', 'image');
        $image = app(ImageRegistry::class)->register('public', 'galleries/content/description.jpg');
        $post = $this->subject('post');
        $post->update(['description' => '<img src="'.$image->url.'">']);
        $partial = $post->newQuery()->select('id', 'content')->findOrFail($post->id);
        $partial->updateQuietly(['content' => 'Normalised']);
        $this->assertDatabaseHas('imageables', ['image_id' => $image->id, 'field' => 'description']);
        Storage::disk('public')->assertExists('galleries/content/description.jpg');
    }
}
