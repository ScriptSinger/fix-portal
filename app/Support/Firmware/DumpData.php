<?php

namespace App\Support\Firmware;

/**
 * Таблица параметров из дампа прошивки (HTML в поле `data`): пары «название, значение» и поля,
 * которые из них достаём. Чистый разбор строки, без базы и модели.
 */
final class DumpData
{
    /** @var array<string, string>|null */
    private ?array $fields = null;

    public function __construct(private readonly string $html)
    {
    }

    public static function from(?string $html): self
    {
        return new self((string) $html);
    }

    /**
     * Все параметры как пары, без служебных строк из дефисов.
     *
     * @return list<array{0: string, 1: string}>
     */
    public function pairs(): array
    {
        preg_match_all('~<tr[^>]*>\s*<td[^>]*>(.*?)</td>\s*<td[^>]*>(.*?)</td>\s*</tr>~su', $this->html, $rows, PREG_SET_ORDER);

        return collect($rows)
            ->map(fn (array $row): array => [$this->clean($row[1]), $this->clean($row[2])])
            ->reject(fn (array $pair): bool => $pair[0] === '' || preg_match('/^[-–—_=\s]+$/u', $pair[0]) || preg_match('/^[-–—_=\s]+$/u', $pair[1]))
            ->values()
            ->all();
    }

    /**
     * Параметры как словарь «название => значение»; при повторе названия берётся первое непустое значение.
     *
     * @return array<string, string>
     */
    public function fields(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $parsed = [];
        if ($this->html !== '') {
            preg_match_all('/<tr[^>]*>\\s*<td[^>]*>(.*?)<\\/td>\\s*<td[^>]*>(.*?)<\\/td>\\s*<\\/tr>/uis', $this->html, $rows, PREG_SET_ORDER);

            foreach ($rows as $row) {
                $label = $this->clean($row[1] ?? '');
                $value = $this->clean($row[2] ?? '');

                if ($label === '') {
                    continue;
                }
                if (! array_key_exists($label, $parsed) || $parsed[$label] === '') {
                    $parsed[$label] = $value;
                }
            }
        }

        return $this->fields = $parsed;
    }

    public function modelName(): ?string
    {
        return $this->field(['Модель', 'Model']);
    }

    public function serialNumber(): ?string
    {
        return $this->field(['S/N', 'SN', 'Серийный номер', 'Serial Number']);
    }

    public function pns(): ?string
    {
        return $this->field(['PNS', 'PNC', 'P/N', 'Part Number']);
    }

    public function firmwareCode(): ?string
    {
        return $this->field(['Код прошивки', 'Firmware Code']);
    }

    public function serialRange(): ?string
    {
        return $this->field(['Для S/N']);
    }

    public function storedError(): ?string
    {
        return $this->errorCode($this->fields()['Сохраненная ошибка'] ?? null);
    }

    public function currentError(): ?string
    {
        return $this->errorCode($this->fields()['Текущая ошибка'] ?? null);
    }

    /** true: контрольная сумма совпадает, false: не совпадает, null: в дампе не указано. */
    public function checksumOk(): ?bool
    {
        $value = (string) ($this->fields()['Контрольная сумма'] ?? '');

        if ($value === '') {
            return null;
        }
        if (str_contains($value, '!!!') || preg_match('/^\s*не\s/ui', $value) === 1) {
            return false;
        }

        return preg_match('/овпадает/u', $value) === 1 ? true : null;
    }

    public function algorithm(): ?string
    {
        $value = trim((string) ($this->fields()['Алгоритм'] ?? ''));

        return $value === '' || mb_strtolower($value) === 'не определен' ? null : $value;
    }

    /** @param list<string> $candidates */
    private function field(array $candidates): ?string
    {
        $data = $this->fields();

        foreach ($candidates as $label) {
            if (! empty($data[$label]) && $this->isRealValue($data[$label])) {
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

    /**
     * Код ошибки из дампа. В данных коды записаны кириллическими «Е», «В» и т. п. (Е94),
     * а ищут их латиницей (E94), поэтому приводим к латинице. «00» и пустое значение означают «ошибки нет».
     */
    private function errorCode(?string $value): ?string
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

    private function clean(string $cell): string
    {
        $decoded = html_entity_decode(strip_tags($cell), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $decoded) ?? '');
    }
}
