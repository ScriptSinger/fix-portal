<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Firmware extends Model
{
    use  SoftDeletes;

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

    public function getParsedDataAttribute(): array
    {
        if (!$this->data) {
            return [];
        }

        $parsed = [];
        preg_match_all('/<tr[^>]*>\\s*<td[^>]*>(.*?)<\\/td>\\s*<td[^>]*>(.*?)<\\/td>\\s*<\\/tr>/uis', $this->data, $rows, PREG_SET_ORDER);

        foreach ($rows as $row) {
            $label = $this->sanitizeCellValue($row[1] ?? '');
            $value = $this->sanitizeCellValue($row[2] ?? '');

            if ($label === '') {
                continue;
            }

            if (!array_key_exists($label, $parsed) || $parsed[$label] === '') {
                $parsed[$label] = $value;
            }
        }

        return $parsed;
    }

    public function getModelNameAttribute(): ?string
    {
        return $this->getParsedField(['Модель', 'Model']);
    }

    public function getSerialNumberAttribute(): ?string
    {
        return $this->getParsedField(['S/N', 'SN', 'Серийный номер', 'Serial Number']);
    }

    public function getPnsAttribute(): ?string
    {
        return $this->getParsedField(['PNS', 'PNC', 'P/N', 'Part Number']);
    }

    public function getFirmwareCodeAttribute(): ?string
    {
        return $this->getParsedField(['Код прошивки', 'Firmware Code']);
    }

    public function getSerialRangeAttribute(): ?string
    {
        return $this->getParsedField(['Для S/N']);
    }

    /**
     * Название для людей и поисковиков: марка и модель, если они известны.
     */
    private function subject(): string
    {
        return trim(($this->brand ? $this->brand . ' ' : '') . ($this->model_name ?: $this->title));
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->subject();
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

    public function getMetaTitleAttribute(): string
    {
        if ($this->model_name && $this->serial_number) {
            return 'Прошивка ' . $this->subject() . ' | S/N ' . $this->serial_number;
        }
        if ($this->model_name && $this->pns) {
            return 'Прошивка ' . $this->subject() . ' | PNS ' . $this->pns;
        }
        if ($this->model_name) {
            return 'Прошивка ' . $this->subject() . ' | ID ' . $this->id;
        }
        return 'Прошивка: ' . $this->title . ' | ID ' . $this->id;
    }

    /**
     * Метаописание из реальных параметров дампа. Отличается у разных прошивок, потому что включает
     * идентификаторы (код прошивки, S/N, PNS), а не общие слова.
     */
    public function getMetaDescriptionAttribute(): string
    {
        $facts = array_filter([
            ($this->platform_norm ?: $this->platform) ? 'платформа ' . ($this->platform_norm ?: $this->platform) : null,
            $this->firmware_code ? 'код прошивки ' . $this->firmware_code : null,
            $this->serial_number ? 'S/N ' . $this->serial_number : null,
            $this->pns ? 'PNS ' . $this->pns : null,
            $this->stored_error ? 'сохранённая ошибка ' . $this->stored_error : null,
            $this->extension ? 'формат ' . $this->extension : null,
            $this->size ? 'размер ' . $this->human_size : null,
        ]);

        $text = 'Прошивка ' . $this->subject() . ($facts ? ': ' . implode(', ', $facts) : '') . '. Скачать файл на UfaMasters.';

        if (mb_strlen($text) <= 160) {
            return $text;
        }

        // Режем по границе слова, а не посреди значения
        $cut = mb_substr($text, 0, 158);
        $space = mb_strrpos($cut, ' ');

        return rtrim(mb_substr($cut, 0, $space ?: 158), ',;: ') . '…';
    }

    public function getHumanSizeAttribute(): string
    {
        $size = (int) $this->size;

        return $size >= 1024 ? number_format($size / 1024, 1, ',', ' ') . ' КБ' : $size . ' Б';
    }

    /**
     * Код ошибки из дампа. В данных коды записаны кириллическими «Е», «В» и т. п. (Е94),
     * а ищут их латиницей (E94), поэтому приводим к латинице. «00» и пустое значение означают «ошибки нет».
     */
    private function normalizeErrorCode(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || in_array(mb_strtolower($value), ['00', '0', 'н.д.', 'нет'], true)) {
            return null;
        }

        $lookalikes = [
            'А' => 'A', 'В' => 'B', 'Е' => 'E', 'К' => 'K', 'М' => 'M', 'Н' => 'H', 'О' => 'O', 'Р' => 'P', 'С' => 'C', 'Т' => 'T', 'Х' => 'X',
            'а' => 'a', 'в' => 'b', 'е' => 'e', 'к' => 'k', 'м' => 'm', 'н' => 'h', 'о' => 'o', 'р' => 'p', 'с' => 'c', 'т' => 't', 'х' => 'x',
        ];

        return mb_strtoupper(strtr($value, $lookalikes));
    }

    public function getStoredErrorAttribute(): ?string
    {
        return $this->normalizeErrorCode($this->parsed_data['Сохраненная ошибка'] ?? null);
    }

    public function getCurrentErrorAttribute(): ?string
    {
        return $this->normalizeErrorCode($this->parsed_data['Текущая ошибка'] ?? null);
    }

    /**
     * true: контрольная сумма совпадает, false: не совпадает, null: в дампе не указано.
     */
    public function getChecksumOkAttribute(): ?bool
    {
        $value = (string) ($this->parsed_data['Контрольная сумма'] ?? '');

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '!!!') || preg_match('/^\s*не\s/ui', $value) === 1) {
            return false;
        }

        return preg_match('/овпадает/u', $value) === 1 ? true : null;
    }

    public function getAlgorithmNameAttribute(): ?string
    {
        $value = trim((string) ($this->parsed_data['Алгоритм'] ?? ''));

        return $value === '' || mb_strtolower($value) === 'не определен' ? null : $value;
    }

    /**
     * Связный текст «О прошивке» из параметров дампа. Только факты из данных, без предположений о совместимости.
     *
     * @return list<string>
     */
    public function getAboutLinesAttribute(): array
    {
        $platform = $this->platform_norm ?: $this->platform;
        $lines = [];

        $intro = 'файл ' . ($this->extension ? 'формата ' . $this->extension : '') . ' размером ' . $this->human_size;
        $intro = 'Прошивка ' . $this->subject() . ': ' . trim($intro);

        if ($platform) {
            $intro .= '; платформа модуля ' . $platform;
            $intro .= $this->brand_group ? ' (' . $this->brand_group . ')' : '';
        }

        if ($this->algorithm_name && $this->algorithm_name !== $platform) {
            $intro .= '; алгоритм ' . $this->algorithm_name;
        }

        $lines[] = $intro . '.';

        $ids = array_filter([
            $this->firmware_code ? 'код прошивки ' . $this->firmware_code : null,
            $this->serial_number ? 'серийный номер ' . $this->serial_number : null,
            $this->pns ? 'PNS ' . $this->pns : null,
        ]);

        if ($ids) {
            $lines[] = 'Идентификаторы в дампе: ' . implode(', ', $ids) . '.';
        }

        $aliases = array_filter(explode("\n", (string) $this->aliases));
        if ($aliases) {
            $lines[] = 'Этот же файл встречается под названиями: ' . implode(', ', array_slice($aliases, 0, 8)) . '.';
        }

        $state = [];
        if ($this->checksum_ok !== null) {
            $state[] = 'контрольная сумма ' . ($this->checksum_ok ? 'совпадает' : 'не совпадает (файл мог быть изменён или повреждён)');
        }
        if ($this->stored_error) {
            $state[] = 'сохранённая ошибка ' . $this->stored_error;
        }
        if ($this->current_error) {
            $state[] = 'текущая ошибка ' . $this->current_error;
        }

        if ($state) {
            $lines[] = 'Состояние дампа: ' . implode('; ', $state) . '.';
        }

        return $lines;
    }

    private function getParsedField(array $candidates): ?string
    {
        $data = $this->parsed_data;

        foreach ($candidates as $label) {
            if (!empty($data[$label]) && $this->isRealValue($data[$label])) {
                return $data[$label];
            }
        }

        return null;
    }

    /**
     * В дампах вместо данных часто стоят заводские заглушки: 0000000000, FFFFFFFF, «н.д.».
     * Выдавать их за серийный номер или код прошивки нельзя.
     */
    private function isRealValue(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || in_array(mb_strtolower($value), ['н.д.', 'н/д', 'n/a', '-', '--', '---', 'не определен'], true)) {
            return false;
        }

        return preg_match('/^(0+|f+|\.+|-+|x+|\?+)$/i', $value) !== 1;
    }

    private function sanitizeCellValue(string $value): string
    {
        $decoded = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\\s+/u', ' ', $decoded) ?? '');
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
