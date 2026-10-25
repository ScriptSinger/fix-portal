<?php

namespace Tests\Unit;

use App\Services\FirmwareCatalog;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FirmwareCatalogTest extends TestCase
{
    public function test_brand_slugs_are_built_from_the_rules(): void
    {
        $slugs = FirmwareCatalog::brandSlugs();

        $this->assertSame('Indesit', $slugs['indesit']);
        $this->assertSame('Hotpoint-Ariston', $slugs['hotpoint-ariston']);
        $this->assertSame('AEG', $slugs['aeg']);
        $this->assertSame('Electrolux', $slugs['electrolux']);
        $this->assertSame('Zanussi', $slugs['zanussi']);
    }

    public function test_slug_and_name_round_trip(): void
    {
        foreach (FirmwareCatalog::brandSlugs() as $slug => $name) {
            $this->assertSame($slug, FirmwareCatalog::slug($name));
            $this->assertSame($name, FirmwareCatalog::brandName($slug));
        }

        $this->assertNull(FirmwareCatalog::brandName('unknown-brand'));
    }

    public function test_hub_urls_are_readable(): void
    {
        $this->assertStringEndsWith('/firmwares/indesit', route('firmwares.brand', ['brand' => 'indesit'], false));
        $this->assertStringEndsWith('/firmwares/indesit/wisl', route('firmwares.series', ['brand' => 'indesit', 'series' => 'wisl'], false));
    }

    public function test_hub_routes_do_not_swallow_firmware_pages(): void
    {
        $router = app('router');

        // /firmwares/285 по-прежнему страница прошивки, а не марка
        $show = $router->getRoutes()->match(request()->create('/firmwares/285'));
        $this->assertSame('firmwares.show', $show->getName());

        $brand = $router->getRoutes()->match(request()->create('/firmwares/indesit'));
        $this->assertSame('firmwares.brand', $brand->getName());

        $series = $router->getRoutes()->match(request()->create('/firmwares/hotpoint-ariston/avl'));
        $this->assertSame('firmwares.series', $series->getName());
    }

    public function test_unknown_brand_or_series_format_is_not_routed_to_a_hub(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        app('router')->getRoutes()->match(request()->create('/firmwares/no-such-brand'));
    }

    public function test_series_slug_must_be_lowercase_letters(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        // Цифры в серии не допускаем: серия это буквенный префикс модели
        app('router')->getRoutes()->match(request()->create('/firmwares/indesit/wisl105'));
    }
}
