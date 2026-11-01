<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\ArticleHtmlNormalizer;
use Illuminate\Console\Command;

class NormalizeArticleContent extends Command
{
    protected $signature = 'articles:normalize-content
        {slugs?* : Слаги статей для обработки}
        {--all : Обработать все статьи}
        {--write : Сохранить нормализованный HTML в базе данных}';

    protected $description = 'Нормализует HTML статей и удаляет визуальные стили из контента';

    public function handle(): int
    {
        $slugs = $this->argument('slugs') ?: [];
        if ($slugs === [] && ! $this->option('all')) {
            $this->error('Укажите слаги статей или используйте --all.');
            return self::FAILURE;
        }

        $query = Post::query()->select(['id', 'slug', 'content']);
        if ($slugs !== []) {
            $query->whereIn('slug', $slugs);
        }

        $posts = $query->get();

        if ($posts->isEmpty()) {
            $this->error('Статьи не найдены.');
            return self::FAILURE;
        }

        foreach ($posts as $post) {
            $normalized = ArticleHtmlNormalizer::normalize($post->content);
            $changed = $normalized !== $post->content;
            $tablesBefore = substr_count($post->content, '<table');
            $tablesAfter = substr_count($normalized, '<table');

            $this->line(sprintf(
                '%s: %s, таблиц %d → %d',
                $post->slug,
                $changed ? 'HTML изменён' : 'без изменений',
                $tablesBefore,
                $tablesAfter
            ));

            if ($changed && $this->option('write')) {
                $post->updateQuietly(['content' => $normalized]);
                $this->info('  сохранено');
            }
        }

        if (! $this->option('write')) {
            $this->comment('Режим просмотра: база не изменена. Для сохранения добавьте --write.');
        }

        return self::SUCCESS;
    }
}
