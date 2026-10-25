<?php

namespace Tests\Unit;

use Tests\TestCase;

class FirmwareBrandRulesTest extends TestCase
{
    /**
     * @return list<string> марки, чьи правила подходят под семейство
     */
    private function brandsFor(string $series): array
    {
        $brands = [];

        foreach (config('firmware_brands.families') as $pattern => $brand) {
            if (preg_match($pattern, $series) === 1) {
                $brands[] = $brand;
            }
        }

        return $brands;
    }

    public function test_known_families_map_to_one_brand(): void
    {
        $expected = [
            'LAV' => 'AEG', 'L' => 'AEG', 'EWF' => 'Electrolux', 'ZWF' => 'Zanussi',
            'WISL' => 'Indesit', 'IWSC' => 'Indesit', 'W' => 'Indesit',
            'AVL' => 'Hotpoint-Ariston', 'AVXL' => 'Hotpoint-Ariston', 'AML' => 'Hotpoint-Ariston',
            'AQXF' => 'Hotpoint-Ariston', 'ARSL' => 'Hotpoint-Ariston', 'AL' => 'Hotpoint-Ariston',
        ];

        foreach ($expected as $series => $brand) {
            $this->assertSame([$brand], $this->brandsFor($series), "семейство {$series}");
        }
    }

    public function test_no_family_matches_two_rules(): void
    {
        foreach (array_keys(config('firmware_brands.ambiguous_families')) as $series) {
            // Неоднозначные префиксы не должны быть в общем списке, иначе марка выберется без учёта платформы
            $this->assertSame([], $this->brandsFor($series), "{$series} есть и в families, и в ambiguous_families");
        }
    }

    public function test_unconfirmed_families_stay_undefined(): void
    {
        foreach (['FE', 'FA', 'AB', 'ALS', 'CM', 'CS', 'SW'] as $series) {
            $this->assertSame([], $this->brandsFor($series), "{$series} не должен угадываться");
        }
    }

    public function test_platform_groups_and_ambiguous_ewc(): void
    {
        $this->assertSame('Electrolux Group', config('firmware_brands.platform_groups.EWM'));
        $this->assertSame('Indesit Company', config('firmware_brands.platform_groups.ARCADIA'));
        $this->assertSame('Electrolux', config('firmware_brands.ambiguous_families.EWC.EWM'));
        $this->assertSame('Indesit', config('firmware_brands.ambiguous_families.EWC.ARCADIA'));
    }
}
