<?php

namespace App\Console\Commands;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Firmware;
use App\Models\Post;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Services\FirmwareCatalog;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml by crawling the website using Spatie\\SitemapGenerator.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $outputPath = public_path('sitemap.xml');
        $sitemap = Sitemap::create();

        $this->addStaticUrls($sitemap);
        $this->addModelUrls($sitemap);

        $sitemap->writeToFile($outputPath);

        $this->info("Sitemap successfully generated: {$outputPath}");
    }

    private function addStaticUrls(Sitemap $sitemap): void
    {
        $sitemap->add($this->makeUrl(route('articles.index'), now(), 1.0, Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add($this->makeUrl(route('about'), now(), 0.6, Url::CHANGE_FREQUENCY_MONTHLY));
        // $sitemap->add($this->makeUrl(route('users.index'), now(), 0.5, Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add($this->makeUrl(route('tags.index'), now(), 0.5, Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add($this->makeUrl(route('questions.index'), now(), 0.8, Url::CHANGE_FREQUENCY_DAILY));
        $sitemap->add($this->makeUrl(route('firmwares.index'), now(), 0.9, Url::CHANGE_FREQUENCY_DAILY));
    }

    private function addModelUrls(Sitemap $sitemap): void
    {
        Post::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($posts) use ($sitemap) {
                foreach ($posts as $post) {
                    $sitemap->add(
                        $this->makeUrl(
                            route('articles.show', ['article' => $post->slug]),
                            $post->updated_at,
                            0.9,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        Category::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($categories) use ($sitemap) {
                foreach ($categories as $category) {
                    $sitemap->add(
                        $this->makeUrl(
                            route('categories.show', ['category' => $category->slug]),
                            $category->updated_at,
                            0.7,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        Tag::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($tags) use ($sitemap) {
                foreach ($tags as $tag) {
                    $sitemap->add(
                        $this->makeUrl(
                            route('tags.show', ['tag' => $tag->slug]),
                            $tag->updated_at,
                            0.5,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        Question::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($questions) use ($sitemap) {
                foreach ($questions as $question) {
                    $sitemap->add(
                        $this->makeUrl(
                            route('questions.show', ['question' => $question->slug]),
                            $question->updated_at,
                            0.8,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        Appliance::query()
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($appliances) use ($sitemap) {
                foreach ($appliances as $appliance) {
                    $sitemap->add(
                        $this->makeUrl(
                            route('public.appliances.show', ['appliance' => $appliance->slug]),
                            $appliance->updated_at,
                            0.7,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        // Страницы-хабы: марки и серии (в серию попадают только достаточно крупные)
        $catalog = app(FirmwareCatalog::class);
        foreach ($catalog->brands() as $brand) {
            $sitemap->add($this->makeUrl(
                route('firmwares.brand', ['brand' => $brand['slug']]),
                now(),
                0.8,
                Url::CHANGE_FREQUENCY_WEEKLY
            ));

            foreach ($catalog->seriesList($brand['name']) as $row) {
                $sitemap->add($this->makeUrl(
                    route('firmwares.series', ['brand' => $brand['slug'], 'series' => $row['slug']]),
                    now(),
                    0.7,
                    Url::CHANGE_FREQUENCY_WEEKLY
                ));
            }
        }

        // Дубли (тот же MD5) указывают canonical на первую запись, в sitemap оставляем только её
        $firstByHash = Firmware::query()
            ->whereNotNull('crc32')
            ->selectRaw('crc32, min(id) as first_id')
            ->groupBy('crc32')
            ->pluck('first_id', 'crc32');

        // Страницы прошивок без содержательных параметров закрыты от индекса, в sitemap им не место
        Firmware::query()
            ->select(['id', 'title', 'extension', 'data', 'crc32', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($firmwares) use ($sitemap, $firstByHash) {
                foreach ($firmwares as $firmware) {
                    if ($firmware->is_thin || (($firstByHash[$firmware->crc32] ?? $firmware->id) !== $firmware->id)) {
                        continue;
                    }

                    $sitemap->add(
                        $this->makeUrl(
                            route('firmwares.show', ['firmware' => $firmware->id]),
                            $firmware->updated_at,
                            0.8,
                            Url::CHANGE_FREQUENCY_WEEKLY
                        )
                    );
                }
            });

        // User::query()
        //     ->select(['id', 'updated_at'])
        //     ->orderBy('id')
        //     ->chunk(500, function ($users) use ($sitemap) {
        //         foreach ($users as $user) {
        //             $sitemap->add(
        //                 $this->makeUrl(
        //                     route('users.show', ['user' => $user->id]),
        //                     $user->updated_at,
        //                     0.4,
        //                     Url::CHANGE_FREQUENCY_MONTHLY
        //                 )
        //             );
        //         }
        //     });
    }

    private function makeUrl(string $location, $lastModificationDate, float $priority, string $changeFrequency): Url
    {
        $url = Url::create($location)
            ->setPriority($priority)
            ->setChangeFrequency($changeFrequency);

        if ($lastModificationDate) {
            $url->setLastModificationDate($lastModificationDate);
        }

        return $url;
    }
}
