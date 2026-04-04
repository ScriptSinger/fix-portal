<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function getMetaTitleAttribute(): string
    {
        if ($this->model_name && $this->serial_number) {
            return $this->model_name . ' | S/N ' . $this->serial_number;
        }
        if ($this->model_name && $this->pns) {
            return $this->model_name . ' | PNS ' . $this->pns;
        }
        if ($this->model_name) {
            return $this->model_name . ' | ID ' . $this->id;
        }
        return 'Прошивка: ' . $this->title . ' | ID ' . $this->id;
    }

    public function getMetaDescriptionAttribute(): string
    {
        $parts = [
            'Прошивка ' . $this->title,
        ];

        if ($this->model_name) {
            $parts[] = 'Модель: ' . $this->model_name;
        }
        if ($this->serial_number) {
            $parts[] = 'S/N: ' . $this->serial_number;
        }
        if ($this->pns) {
            $parts[] = 'PNS: ' . $this->pns;
        }
        if ($this->firmware_code) {
            $parts[] = 'Код прошивки: ' . $this->firmware_code;
        }
        if ($this->platform) {
            $parts[] = 'Платформа: ' . $this->platform;
        }
        if ($this->extension) {
            $parts[] = 'Формат: ' . $this->extension;
        }

        $text = implode('. ', $parts) . '.';
        return mb_substr($text, 0, 160);
    }

    private function getParsedField(array $candidates): ?string
    {
        $data = $this->parsed_data;

        foreach ($candidates as $label) {
            if (!empty($data[$label])) {
                return $data[$label];
            }
        }

        return null;
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
