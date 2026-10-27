<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\FirmwareCatalog;
use App\Support\Firmware\DumpData;
use App\Support\Firmware\FirmwareText;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Firmware extends Model
{
    use SoftDeletes;

    private ?DumpData $dumpCache = null;
    private ?string $dumpSource = null;

    protected $fillable = [
        'title',
        'path_id',
        'size',
        'date',
        'extension',
        'platform',
        'crc32',
        'file_path',
        'file_disk',
        'aliases',
        'merged_into',
        'data',
        'views_count',
        'downloads_count',
    ];

    /** Разобранная таблица параметров дампа; пересоздаётся, если поле data изменили. */
    public function dump(): DumpData
    {
        if ($this->dumpCache === null || $this->dumpSource !== (string) $this->data) {
            $this->dumpSource = (string) $this->data;
            $this->dumpCache = DumpData::from($this->dumpSource);
        }

        return $this->dumpCache;
    }

    /** Тексты страницы (название, метатеги, «О прошивке»). */
    public function text(): FirmwareText
    {
        return new FirmwareText($this, $this->dump());
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Имя диска, на котором лежит файл. null в БД означает локальный диск public.
     */
    public function getDiskNameAttribute(): string
    {
        return $this->file_disk ?: 'public';
    }

    /**
     * Путь (ключ) файла на его диске. На локальном диске это firmwares/{file_path}
     * (для записей без file_path осталось прежнее правило title + extension),
     * в облаке file_path уже содержит полный ключ.
     */
    public function getStoragePathAttribute(): string
    {
        if ($this->file_disk) {
            return (string) $this->file_path;
        }

        return 'firmwares/' . ($this->file_path ?: $this->title . ($this->extension ?? ''));
    }

    /**
     * Есть ли файл. Для облака не ходим за ним по сети на каждый просмотр страницы:
     * наличие проверено при переносе и при загрузке через админку.
     */
    public function getHasFileAttribute(): bool
    {
        return $this->file_disk
            ? true
            : Storage::disk('public')->exists($this->storage_path);
    }

    /**
     * Страница без содержательных параметров (нет модели, серийного номера, PNS и кода прошивки):
     * кроме имени файла сказать о ней нечего, такие страницы не нужны поисковикам.
     */
    public function getIsThinAttribute(): bool
    {
        return ! $this->model_name
            && ! $this->serial_number
            && ! $this->pns
            && ! $this->firmware_code;
    }

    /**
     * Имя, под которым файл скачивает пользователь (на диске имя может быть хешем).
     */
    public function getDownloadNameAttribute(): string
    {
        return $this->title . ($this->extension ?? '');
    }

    // Параметры дампа и тексты считают DumpData и FirmwareText; здесь только короткие свойства для шаблонов
    public function getParameterPairsAttribute(): array
    {
        return $this->dump()->pairs();
    }

    public function getParsedDataAttribute(): array
    {
        return $this->dump()->fields();
    }

    public function getModelNameAttribute(): ?string
    {
        return $this->dump()->modelName();
    }

    public function getSerialNumberAttribute(): ?string
    {
        return $this->dump()->serialNumber();
    }

    public function getPnsAttribute(): ?string
    {
        return $this->dump()->pns();
    }

    public function getFirmwareCodeAttribute(): ?string
    {
        return $this->dump()->firmwareCode();
    }

    public function getSerialRangeAttribute(): ?string
    {
        return $this->dump()->serialRange();
    }

    public function getStoredErrorAttribute(): ?string
    {
        return $this->dump()->storedError();
    }

    public function getCurrentErrorAttribute(): ?string
    {
        return $this->dump()->currentError();
    }

    public function getChecksumOkAttribute(): ?bool
    {
        return $this->dump()->checksumOk();
    }

    public function getAlgorithmNameAttribute(): ?string
    {
        return $this->dump()->algorithm();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->text()->displayName();
    }

    public function getMetaTitleAttribute(): string
    {
        return $this->text()->metaTitle();
    }

    public function getMetaDescriptionAttribute(): string
    {
        return $this->text()->metaDescription();
    }

    public function getHumanSizeAttribute(): string
    {
        return $this->text()->humanSize();
    }

    public function getAboutLinesAttribute(): array
    {
        return $this->text()->aboutLines();
    }

    /**
     * Первая запись с таким же содержимым (MD5). Остальные считаются дублями и указывают на неё через canonical.
     */
    public function getCanonicalIdAttribute(): int
    {
        if (! preg_match('/^[a-f0-9]{32}$/i', (string) $this->crc32)) {
            return (int) $this->id;
        }

        return (int) (static::query()->where('crc32', $this->crc32)->min('id') ?: $this->id);
    }

    /**
     * Адрес страницы серии (/firmwares/indesit/wisl) или null, если у серии нет своей страницы.
     */
    public function getHubUrlAttribute(): ?string
    {
        if (! $this->brand || ! $this->series || ! app(FirmwareCatalog::class)->hasSeriesHub($this->brand, $this->series)) {
            return null;
        }

        return route('firmwares.series', ['brand' => FirmwareCatalog::slug($this->brand), 'series' => strtolower($this->series)]);
    }

    /**
     * Сколько прошивок той же марки и серии в каталоге (кэш на 10 минут).
     */
    public function seriesCount(): int
    {
        if (! $this->brand || ! $this->series) {
            return 0;
        }

        return Cache::remember(
            'firmware.series_count.' . md5($this->brand . '|' . $this->series),
            600,
            fn () => static::query()->where('brand', $this->brand)->where('series', $this->series)->count()
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($firmware) {
            $firmware->comments()->each(function ($comment) {
                $comment->replies()->delete();
            });
            $firmware->comments()->delete();
        });

        static::restoring(function ($firmware) {
            $firmware->comments()->withTrashed()->each(function ($comment) {
                $comment->replies()->withTrashed()->restore();
            });
            $firmware->comments()->withTrashed()->restore();
        });
    }
}
