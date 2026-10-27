<?php

namespace Tests\Feature;

use App\Models\Firmware;
use App\Models\FirmwareRedirect;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Каталог прошивок: фильтры, поиск, сортировка, страница, скачивание. Описывает поведение, которое нельзя ломать при рефакторинге. */
class FirmwareCatalogPageTest extends TestCase
{
    use DatabaseTransactions;

    private function make(array $attrs = []): Firmware
    {
        return Firmware::forceCreate($attrs + ['path_id' => random_int(1_000_000, 9_999_999), 'title' => 'UNIQ' . uniqid(), 'extension' => '.bin', 'size' => 2048]);
    }

    private function row(string $title, array $over = []): Firmware
    {
        return $this->make($over + ['title' => $title]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_index_lists_firmwares_and_sorts_with_a_whitelist(): void
    {
        $a = $this->row('ZZ_ALPHA_' . uniqid());
        $b = $this->row('AA_BETA_' . uniqid());

        $response = $this->get(route('firmwares.index', ['search' => 'AA_BETA_', 'sort' => 'title', 'dir' => 'asc']))->assertOk();
        $response->assertSee($b->title)->assertDontSee($a->title);

        // неизвестные сортировка и направление не ломают страницу
        $this->get(route('firmwares.index', ['sort' => 'password; drop', 'dir' => 'sideways', 'per_page' => 7]))->assertOk();
    }

    public function test_filters_by_brand_series_platform_extension_and_crc(): void
    {
        $match = $this->row('MATCH_' . uniqid(), ['brand' => 'Indesit', 'series' => 'WISL', 'platform_norm' => 'Arcadia', 'platform' => 'ARCADIA', 'extension' => '.eep', 'crc32' => 'abc123']);
        $other = $this->row('OTHER_' . uniqid(), ['brand' => 'Ariston', 'series' => 'AQ', 'platform_norm' => 'Evo', 'platform' => 'EVO', 'extension' => '.bin', 'crc32' => 'zzz999']);

        foreach ([['brand' => 'Indesit'], ['series' => 'wisl'], ['platform' => 'Arcadia'], ['extension' => '.eep'], ['crc32' => 'abc123']] as $filter) {
            $this->get(route('firmwares.index', $filter))->assertOk()->assertSee($match->title)->assertDontSee($other->title);
        }
    }

    public function test_platform_filter_also_matches_the_old_platform_field(): void
    {
        $old = $this->row('OLDPLAT_' . uniqid(), ['platform' => 'LEGACYPLAT']);

        $this->get(route('firmwares.index', ['platform' => 'LEGACYPLAT']))->assertOk()->assertSee($old->title);
    }

    public function test_search_finds_by_title_model_and_ignores_separators(): void
    {
        $f = $this->row('WISL105_' . uniqid(), ['model' => 'WISL105CSI', 'series' => 'WISL105', 'brand' => 'Indesit']);

        foreach (['WISL105_', 'wisl 105csi', 'WISL-105CSI'] as $term) {
            $this->get(route('firmwares.index', ['search' => $term]))->assertOk()->assertSee($f->title);
        }
        $this->get(route('firmwares.index', ['search' => (string) $f->id]))->assertOk()->assertSee($f->title);
    }

    public function test_search_looks_into_the_dump_table_when_short_fields_find_nothing(): void
    {
        $f = $this->row('NODATA_' . uniqid(), ['data' => '<table><tr><td>S/N</td><td>SN9988776655</td></tr></table>']);

        $this->get(route('firmwares.index', ['search' => 'SN9988776655']))->assertOk()->assertSee($f->title);
    }

    public function test_search_treats_percent_and_underscore_literally(): void
    {
        $this->row('PLAINTITLE' . uniqid());

        $this->get(route('firmwares.index', ['search' => '%']))->assertOk()->assertDontSee('PLAINTITLE');
    }

    public function test_show_counts_views_and_shows_meta(): void
    {
        $f = $this->row('SHOWME_' . uniqid(), ['views_count' => 0]);

        $this->get(route('firmwares.show', ['firmware' => $f->id]))->assertOk()->assertSee('Прошивка: ' . $f->title . ' | ID ' . $f->id, false);

        $this->assertSame(1, $f->refresh()->views_count);
    }

    public function test_merged_duplicate_redirects_permanently(): void
    {
        $main = $this->row('MAIN_' . uniqid());
        FirmwareRedirect::create(['old_id' => 987654001, 'new_id' => $main->id]);

        $this->get(route('firmwares.show', ['firmware' => 987654001]))->assertStatus(301)->assertRedirect(route('firmwares.show', ['firmware' => $main->id]));
        $this->get(route('firmwares.show', ['firmware' => 987654321]))->assertNotFound();
    }

    public function test_download_needs_a_verified_login(): void
    {
        $f = $this->row('DL_' . uniqid());

        $this->get(route('firmwares.download', ['firmware' => $f->id]))->assertRedirect(route('login'));
    }

    public function test_download_serves_a_local_file_and_counts_it(): void
    {
        Storage::fake('public');
        $f = $this->row('DLOK_' . uniqid(), ['downloads_count' => 0]);
        Storage::disk('public')->put($f->storage_path, 'BINARY');

        $response = $this->actingAs(User::factory()->create())->get(route('firmwares.download', ['firmware' => $f->id]))->assertOk();

        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertSame(1, $f->refresh()->downloads_count);
    }

    public function test_download_of_a_missing_file_goes_back_with_an_error(): void
    {
        Storage::fake('public');
        $f = $this->row('DLMISS_' . uniqid());

        $this->actingAs(User::factory()->create())->from('/firmwares')->get(route('firmwares.download', ['firmware' => $f->id]))
            ->assertRedirect('/firmwares')->assertSessionHas('error', 'Файл не найден');
    }

    public function test_download_of_a_merged_duplicate_redirects_to_the_main_record(): void
    {
        $main = $this->row('MAIND_' . uniqid());
        FirmwareRedirect::create(['old_id' => 987654002, 'new_id' => $main->id]);

        $this->actingAs(User::factory()->create())->get(route('firmwares.download', ['firmware' => 987654002]))
            ->assertStatus(301)->assertRedirect(route('firmwares.download', ['firmware' => $main->id]));
    }
}
