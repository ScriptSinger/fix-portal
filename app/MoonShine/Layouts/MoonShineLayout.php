<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\AccessLog\AccessLogResource;
use App\MoonShine\Resources\ApplianceResource;
use App\MoonShine\Resources\BlockedUserAgentResource;
use App\MoonShine\Resources\BlockerResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\CustomizationResource;
use App\MoonShine\Resources\FirmwareResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\PostResource;
use App\MoonShine\Resources\QuestionAnnouncementResource;
use App\MoonShine\Resources\QuestionResource;
use App\MoonShine\Resources\SentEmailResource;
use App\MoonShine\Resources\SiteSettingResource;
use App\MoonShine\Resources\TagResource;
use App\MoonShine\Resources\UserResource;
use App\MoonShine\Resources\VisitorResource;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Components\Layout\Layout;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            MenuGroup::make('Статьи', [
                MenuItem::make(PostResource::class, 'Статьи')->icon('newspaper'),
                MenuItem::make(CategoryResource::class, 'Категории')->icon('folder'),
                MenuItem::make(TagResource::class, 'Теги')->icon('tag'),
            ], 'newspaper'),
            MenuGroup::make('Вопросы', [
                MenuItem::make(QuestionResource::class, 'Вопросы')->icon('question-mark-circle'),
                MenuItem::make(ApplianceResource::class, 'Типы техники')->icon('wrench-screwdriver'),
            ], 'question-mark-circle'),
            MenuGroup::make('Каталог', [
                MenuItem::make(FirmwareResource::class, 'Прошивки')->icon('cpu-chip'),
            ], 'rectangle-stack'),
            MenuGroup::make('Обсуждения', [
                MenuItem::make(CommentResource::class, 'Комментарии и ответы')->icon('chat-bubble-left-right'),
            ], 'chat-bubble-left-right'),
            MenuGroup::make('Рассылки', [
                MenuItem::make(QuestionAnnouncementResource::class, 'Публикации вопросов')->icon('paper-airplane'),
                MenuItem::make(SentEmailResource::class, 'Отправленные письма')->icon('envelope'),
            ], 'megaphone'),
            MenuGroup::make('Пользователи', [
                MenuItem::make(UserResource::class, 'Пользователи сайта')->icon('users'),
                MenuItem::make(ImageResource::class, 'Изображения')->icon('photo'),
                MenuItem::make(VisitorResource::class, 'Посетители')->icon('eye'),
            ], 'user-group'),
            MenuGroup::make('Безопасность', [
                MenuItem::make(AccessLogResource::class, 'Логи запросов')->icon('globe-alt'),
                MenuItem::make(BlockerResource::class, 'Заблокированные IP')->icon('x-circle'),
                MenuItem::make(BlockedUserAgentResource::class, 'Заблокированные User-Agent')->icon('bug-ant'),
            ], 'shield-check'),
            MenuGroup::make('Настройки', [
                MenuItem::make(CustomizationResource::class, 'Кастомизация')->icon('paint-brush'),
                MenuItem::make(SiteSettingResource::class, 'Настройки сайта')->icon('cog-6-tooth'),
            ], 'cog-6-tooth'),
            MenuItem::make(MoonShineUserResource::class, 'Администраторы')->icon('users'),
        ];
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
