<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\ApplianceResource;
use App\MoonShine\Resources\BlockedUserAgentResource;
use App\MoonShine\Resources\BlockerResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\CommentResource;
use App\MoonShine\Resources\CtaResource;
use App\MoonShine\Resources\CustomizationResource;
use App\MoonShine\Resources\FirmwareResource;
use App\MoonShine\Resources\ImageResource;
use App\MoonShine\Resources\PostResource;
use App\MoonShine\Resources\QuestionResource;
use App\MoonShine\Resources\ReplyResource;
use App\MoonShine\Resources\RequestLogResource;
use App\MoonShine\Resources\TagResource;
use App\MoonShine\Resources\UserResource;
use App\Http\Controllers\MoonShine\TinyMceImageUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  CoreContract<MoonShine>  $core
     */
    public function boot(CoreContract $core): void
    {
        // Вне группы `web`, иначе куки шифруются дважды и теряется сессия.
        Route::middleware(array_merge(['moonshine'], config('moonshine.auth.middleware', [])))
            ->prefix(config('moonshine.prefix'))
            ->group(function (): void {
                Route::post('/tinymce/upload', TinyMceImageUploadController::class)
                    ->name('moonshine.tinymce.upload');
            });

        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                PostResource::class,
                CategoryResource::class,
                TagResource::class,
                CtaResource::class,
                QuestionResource::class,
                CommentResource::class,
                ReplyResource::class,
                ApplianceResource::class,
                FirmwareResource::class,
                UserResource::class,
                ImageResource::class,
                RequestLogResource::class,
                BlockerResource::class,
                BlockedUserAgentResource::class,
                CustomizationResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
            ])
        ;
    }
}
