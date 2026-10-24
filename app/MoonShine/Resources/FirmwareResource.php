<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Firmware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\UI\Fields\File;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('cpu-chip')]
#[Group('Каталог', 'rectangle-stack')]
#[Order(6)]
/**
 * @extends ModelResource<Firmware>
 */
class FirmwareResource extends BaseResource
{
    protected string $model = Firmware::class;

    protected string $column = 'title';

    protected bool $simplePaginate = true;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Прошивки';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название', 'title')->sortable(),
            Text::make('Платформа', 'platform')->sortable(),
            Text::make('Формат', 'extension'),
            Number::make('Размер', 'size')->sortable(),
            Date::make('Дата', 'date')->format('d.m.Y')->sortable(),
            Number::make('Просмотры', 'views_count')->sortable(),
            Number::make('Скачивания', 'downloads_count')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('CRC32', 'crc32'),
            Textarea::make('Данные', 'data')->unescape(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            // Файл выбирают только при создании: название, формат, размер и хеш берутся из него
            File::make('Файл прошивки (bin, eep, s19, hex, до 1 МБ)', 'upload')
                ->onApply(static fn ($item) => $item)
                ->canSee(fn () => ! ($this->getItem()?->exists ?? false))
                ->required(),
            Text::make('Название', 'title')
                ->hint('Если оставить пустым, возьмётся имя файла без расширения'),
            Text::make('Платформа', 'platform'),
            Date::make('Дата', 'date')->default(now()->toDateString()),
            Textarea::make('Данные (HTML)', 'data')->customAttributes(['rows' => 14]),
            Text::make('Формат', 'extension')->readonly()
                ->canSee(fn () => $this->getItem()?->exists ?? false),
            Text::make('MD5', 'crc32')->readonly()
                ->canSee(fn () => $this->getItem()?->exists ?? false),
        ];
    }

    protected function rules($item): array
    {
        $creating = ! ($item?->exists ?? false);

        return [
            'upload' => $creating
                ? ['required', 'file', 'extensions:bin,eep,s19,hex', 'max:1024', $this->notAlreadyUploaded(...)]
                : ['nullable'],
            'title' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'data' => ['nullable', 'string'],
        ];
    }

    /**
     * Прошивка с таким содержимым уже есть: не плодим дубли.
     */
    private function notAlreadyUploaded(string $attribute, mixed $file, \Closure $fail): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $existing = Firmware::where('crc32', md5_file($file->getRealPath()))->first();

        if ($existing) {
            $fail("Такая прошивка уже есть: №{$existing->id} «{$existing->title}».");
        }
    }

    /**
     * Файл уходит в облако под ключом по MD5, а запись получает размер, формат и хеш из него.
     */
    protected function beforeCreating(DataWrapperContract $item): DataWrapperContract
    {
        /** @var Firmware $firmware */
        $firmware = $item->getOriginal();
        $file = request()->file('upload');

        if (! $file instanceof UploadedFile) {
            return $item;
        }

        $md5 = md5_file($file->getRealPath());
        $key = 'firmwares/' . substr($md5, 0, 2) . '/' . $md5;
        $disk = Storage::disk('firmware_s3');

        if (! $disk->exists($key)) {
            $stream = fopen($file->getRealPath(), 'rb');
            $disk->writeStream($key, $stream);
            is_resource($stream) && fclose($stream);

            if ($disk->size($key) !== $file->getSize()) {
                $disk->delete($key);

                throw new \RuntimeException('Файл не загрузился в облако полностью, попробуйте ещё раз.');
            }
        }

        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $firmware->title = filled($firmware->title) ? $firmware->title : $name;
        $firmware->extension = '.' . strtolower($file->getClientOriginalExtension());
        $firmware->size = $file->getSize();
        $firmware->crc32 = $md5;
        $firmware->file_disk = 'firmware_s3';
        $firmware->file_path = $key;
        $firmware->date ??= now()->toDateString();

        return $item;
    }

    protected function search(): array
    {
        return ['id', 'title', 'crc32'];
    }
}
