<?php

namespace App\Support\Firmware;

use App\Models\Firmware;

/**
 * Тексты страницы прошивки для людей и поисковиков: название, метатеги, блок «О прошивке».
 * Только факты из данных дампа и каталога, без предположений о совместимости.
 */
final class FirmwareText
{
    public function __construct(private readonly Firmware $firmware, private readonly DumpData $dump)
    {
    }

    /** Название: марка и модель, если они известны. */
    public function displayName(): string
    {
        $f = $this->firmware;

        return trim(($f->brand ? $f->brand . ' ' : '') . ($this->dump->modelName() ?: $f->title));
    }

    public function metaTitle(): string
    {
        $f = $this->firmware;
        $model = $this->dump->modelName();

        if ($model && $this->dump->serialNumber()) {
            return 'Прошивка ' . $this->displayName() . ' | S/N ' . $this->dump->serialNumber();
        }
        if ($model && $this->dump->pns()) {
            return 'Прошивка ' . $this->displayName() . ' | PNS ' . $this->dump->pns();
        }
        if ($model) {
            return 'Прошивка ' . $this->displayName() . ' | ID ' . $f->id;
        }

        return 'Прошивка: ' . $f->title . ' | ID ' . $f->id;
    }

    /**
     * Метаописание из реальных параметров дампа. Отличается у разных прошивок, потому что включает
     * идентификаторы (код прошивки, S/N, PNS), а не общие слова.
     */
    public function metaDescription(): string
    {
        $f = $this->firmware;
        $platform = $f->platform_norm ?: $f->platform;

        $facts = array_filter([
            $platform ? 'платформа ' . $platform : null,
            $this->dump->firmwareCode() ? 'код прошивки ' . $this->dump->firmwareCode() : null,
            $this->dump->serialNumber() ? 'S/N ' . $this->dump->serialNumber() : null,
            $this->dump->pns() ? 'PNS ' . $this->dump->pns() : null,
            $this->dump->storedError() ? 'сохранённая ошибка ' . $this->dump->storedError() : null,
            $f->extension ? 'формат ' . $f->extension : null,
            $f->size ? 'размер ' . $this->humanSize() : null,
        ]);

        $text = 'Прошивка ' . $this->displayName() . ($facts ? ': ' . implode(', ', $facts) : '') . '. Скачать файл на UfaMasters.';

        if (mb_strlen($text) <= 160) {
            return $text;
        }

        // Режем по границе слова, а не посреди значения
        $cut = mb_substr($text, 0, 158);
        $space = mb_strrpos($cut, ' ');

        return rtrim(mb_substr($cut, 0, $space ?: 158), ',;: ') . '…';
    }

    public function humanSize(): string
    {
        $size = (int) $this->firmware->size;

        return $size >= 1024 ? number_format($size / 1024, 1, ',', ' ') . ' КБ' : $size . ' Б';
    }

    /**
     * Связный текст «О прошивке» из параметров дампа.
     *
     * @return list<string>
     */
    public function aboutLines(): array
    {
        $f = $this->firmware;
        $platform = $f->platform_norm ?: $f->platform;
        $algorithm = $this->dump->algorithm();
        $lines = [];

        $intro = 'файл ' . ($f->extension ? 'формата ' . $f->extension : '') . ' размером ' . $this->humanSize();
        $intro = 'Прошивка ' . $this->displayName() . ': ' . trim($intro);

        if ($platform) {
            $intro .= '; платформа модуля ' . $platform;
            $intro .= $f->brand_group ? ' (' . $f->brand_group . ')' : '';
        }
        if ($algorithm && $algorithm !== $platform) {
            $intro .= '; алгоритм ' . $algorithm;
        }
        $lines[] = $intro . '.';

        $ids = array_filter([
            $this->dump->firmwareCode() ? 'код прошивки ' . $this->dump->firmwareCode() : null,
            $this->dump->serialNumber() ? 'серийный номер ' . $this->dump->serialNumber() : null,
            $this->dump->pns() ? 'PNS ' . $this->dump->pns() : null,
        ]);
        if ($ids) {
            $lines[] = 'Идентификаторы в дампе: ' . implode(', ', $ids) . '.';
        }

        $aliases = array_filter(explode("\n", (string) $f->aliases));
        if ($aliases) {
            $lines[] = 'Этот же файл встречается под названиями: ' . implode(', ', array_slice($aliases, 0, 8)) . '.';
        }

        $state = [];
        if (($ok = $this->dump->checksumOk()) !== null) {
            $state[] = 'контрольная сумма ' . ($ok ? 'совпадает' : 'не совпадает (файл мог быть изменён или повреждён)');
        }
        if ($this->dump->storedError()) {
            $state[] = 'сохранённая ошибка ' . $this->dump->storedError();
        }
        if ($this->dump->currentError()) {
            $state[] = 'текущая ошибка ' . $this->dump->currentError();
        }
        if ($state) {
            $lines[] = 'Состояние дампа: ' . implode('; ', $state) . '.';
        }

        return $lines;
    }
}
