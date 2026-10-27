<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Administrator;
use App\Models\Post;
use App\MoonShine\Support\TinyMceUpload;
use App\Services\ThumbnailService;
use App\MoonShine\Fields\LocalDate;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('newspaper')]
#[Group('Контент', 'document-text')]
#[Order(1)]
/**
 * @extends ModelResource<Post>
 */
class PostResource extends BaseResource
{
    protected string $model = Post::class;

    protected string $column = 'title';

    protected array $with = ['category', 'tags', 'thumbnail', 'administrator'];

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Статьи';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Заголовок', 'title')->sortable(),
            BelongsTo::make('Категория', 'category', 'title', CategoryResource::class),
            Number::make('Просмотры', 'views')->sortable(),
            LocalDate::make('Создана', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('Slug', 'slug'),
            Textarea::make('Описание', 'description'),
            Textarea::make('Содержимое', 'content')->unescape(),
            BelongsToMany::make('Теги', 'tags', 'title', TagResource::class),
            HasMany::make('CTA', 'ctas', resource: CtaResource::class),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Заголовок', 'title')->required(),
            Textarea::make('Описание', 'description')->required(),
            TinyMceUpload::withImageUpload(TinyMce::make('Содержимое', 'content'))
                ->required(),
            BelongsTo::make('Категория', 'category', 'title', CategoryResource::class)
                ->searchable()
                ->required(),
            BelongsToMany::make('Теги', 'tags', 'title', TagResource::class)
                ->selectMode()
                ->searchable(),
            Preview::make('Текущая обложка', 'thumbnail_url')
                ->image()
                ->canSee(fn () => $this->getItem()?->exists ?? false),
            Image::make('Новая обложка', 'thumbnail_file')
                ->allowedExtensions(['jpg', 'jpeg', 'png', 'gif'])
                ->onApply(static fn ($item) => $item),
        ];
    }

    protected function rules($item): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'thumbnail_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    protected function search(): array
    {
        return ['id', 'title'];
    }

    protected function filters(): iterable
    {
        return [
            BelongsTo::make('Категория', 'category', 'title', CategoryResource::class)->nullable(),
        ];
    }

    protected function beforeCreating(DataWrapperContract $item): DataWrapperContract
    {
        $this->assignAdministrator($item->getOriginal());

        return $item;
    }

    protected function beforeUpdating(DataWrapperContract $item): DataWrapperContract
    {
        $this->assignAdministrator($item->getOriginal());

        return $item;
    }

    protected function afterCreated(DataWrapperContract $item): DataWrapperContract
    {
        $this->storeThumbnail($item->getOriginal());

        return $item;
    }

    protected function afterUpdated(DataWrapperContract $item): DataWrapperContract
    {
        $this->storeThumbnail($item->getOriginal());

        return $item;
    }

    /**
     * Статья всё ещё ссылается на таблицу administrators, поэтому автором
     * ставим администратора с тем же e-mail, а если такого нет — первого.
     */
    private function assignAdministrator(Post $post): Post
    {
        $email = auth(config('moonshine.auth.guard'))->user()?->email;

        $post->administrator_id = Administrator::where('email', $email)->value('id')
            ?? $post->administrator_id
            ?? Administrator::query()->value('id');

        return $post;
    }

    private function storeThumbnail(Post $post): Post
    {
        $file = request()->file('thumbnail_file');

        if (! $file) {
            return $post;
        }

        $paths = $post->thumbnail
            ? ThumbnailService::getInstance()->destroy($this->thumbnailPaths($post))
            : ThumbnailService::getInstance();

        $post->thumbnail()->updateOrCreate([], $paths->store($file));

        return $post;
    }

    /**
     * В БД лежат URL (/storage/...), а диск ожидает относительные пути.
     */
    private function thumbnailPaths(Post $post): array
    {
        return collect([
            $post->thumbnail->original,
            $post->thumbnail->blog,
            $post->thumbnail->small,
        ])->map(fn (?string $url) => $url ? preg_replace('#^/?storage/#', '', $url) : null)
            ->filter()
            ->all();
    }
}
