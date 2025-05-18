<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;

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
        $baseUrl = config('app.url');
        $outputPath = public_path('sitemap.xml');

        SitemapGenerator::create($baseUrl)
            ->writeToFile($outputPath);

        $this->info("Sitemap успешно сгенерирован: $outputPath . $baseUrl");
    }
}
