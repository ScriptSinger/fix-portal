<?php

namespace Tests\Unit;

use App\Models\Firmware;
use Tests\TestCase;

class FirmwareTextTest extends TestCase
{
    private function firmware(array $params, array $attributes = []): Firmware
    {
        $rows = '';
        foreach ($params as $label => $value) {
            $rows .= "<tr><td>{$label}</td><td>{$value}</td></tr>";
        }

        return (new Firmware())->forceFill(array_merge([
            'id' => 5,
            'title' => 'TEST_123',
            'extension' => '.eep',
            'size' => 8192,
            'platform' => 'ARCADIA',
            'platform_norm' => 'Arcadia',
            'data' => "<table>{$rows}</table>",
        ], $attributes));
    }

    public function test_cyrillic_error_codes_are_normalized_to_latin(): void
    {
        $f = $this->firmware(['Сохраненная ошибка' => 'Е94', 'Текущая ошибка' => 'ЕВ1']);

        $this->assertSame('E94', $f->stored_error);
        $this->assertSame('EB1', $f->current_error);
    }

    public function test_no_error_values_are_ignored(): void
    {
        $f = $this->firmware(['Сохраненная ошибка' => '00', 'Текущая ошибка' => '']);

        $this->assertNull($f->stored_error);
        $this->assertNull($f->current_error);
    }

    public function test_factory_placeholders_are_not_reported_as_data(): void
    {
        $f = $this->firmware(['Модель' => 'WISL105CSI', 'S/N' => '0000000000', 'Код прошивки' => 'ffffffff', 'PNS' => 'н.д.']);

        $this->assertSame('WISL105CSI', $f->model_name);
        $this->assertNull($f->serial_number);
        $this->assertNull($f->firmware_code);
        $this->assertNull($f->pns);
    }

    public function test_page_without_real_parameters_is_thin(): void
    {
        $this->assertTrue($this->firmware(['S/N' => '0000000000'])->is_thin);
        $this->assertFalse($this->firmware(['Модель' => 'LSTA216'])->is_thin);
    }

    public function test_checksum_status(): void
    {
        $this->assertTrue($this->firmware(['Контрольная сумма' => 'Cовпадает'])->checksum_ok);
        $this->assertFalse($this->firmware(['Контрольная сумма' => '!!! Не совпадает !!!'])->checksum_ok);
        $this->assertNull($this->firmware([])->checksum_ok);
    }

    public function test_meta_description_is_short_and_contains_identifiers(): void
    {
        $f = $this->firmware(
            ['Модель' => 'WISL105CSI', 'S/N' => '31015497600', 'Код прошивки' => '50799800001', 'Сохраненная ошибка' => 'Е94'],
            ['brand' => 'Indesit']
        );

        $description = $f->meta_description;

        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertStringContainsString('Indesit WISL105CSI', $description);
        $this->assertStringContainsString('31015497600', $description);
        $this->assertStringContainsString('E94', $description);
    }

    public function test_human_size(): void
    {
        $this->assertSame('8,0 КБ', $this->firmware([])->human_size);
        $this->assertSame('17 Б', $this->firmware([], ['size' => 17])->human_size);
    }
}
