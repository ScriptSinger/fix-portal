<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use MoonShine\Crud\QueryTags\QueryTag;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\SortDirection;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;

#[Icon('signal')]
#[Group('Пользователи', 'user-group')]
#[Order(8)]
/**
 * Кто и когда последний раз был на сайте (пользователи с подтверждённой почтой, см. TrackUserActivity).
 *
 * @extends ModelResource<User>
 */
class VisitorResource extends BaseResource
{
    public const TAG_ONLINE = 'online';
    public const TAG_TODAY = 'today';
    public const TAG_WEEK = '7d';
    public const TAG_MONTH = '30d';

    protected string $model = User::class;

    protected string $column = 'name';

    protected string $sortColumn = 'last_seen_at';

    protected SortDirection $sortDirection = SortDirection::DESC;

    protected int $itemsPerPage = 50;

    public function getTitle(): string
    {
        return 'Посетители';
    }

    /**
     * Только просмотр: список ведёт в карточку пользователя через ссылку в имени.
     *
     * @return list<class-string>
     */
    protected function pages(): array
    {
        return [IndexPage::class];
    }

    protected function activeActions(): ListOf
    {
        return new ListOf(\MoonShine\Support\Enums\Action::class, []);
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->whereNotNull('last_seen_at');
    }

    protected function indexFields(): iterable
    {
        return [
            Preview::make('Пользователь', 'name', fn (User $user) => '<a href="' . e(app(UserResource::class)->getDetailPageUrl($user->id)) . '" class="underline">' . e($user->name) . '</a>')
                ->sortable(),
            Email::make('E-mail', 'email')->sortable(),
            Preview::make('Статус', 'last_seen_at', fn (User $user) => $this->isOnline($user)
                ? (string) Badge::make('онлайн', 'success')
                : ''),
            Date::make('Последний визит', 'last_seen_at')->format('d.m.Y H:i')->sortable(),
            Preview::make('Это было', 'last_seen_at', fn (User $user) => $user->last_seen_at?->diffForHumans() ?? ''),
            Date::make('Регистрация', 'created_at')->format('d.m.Y')->sortable(),
        ];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make('Онлайн', static fn (Builder $q) => $q->where('last_seen_at', '>=', now()->subMinutes(User::ONLINE_MINUTES)))
                ->alias(self::TAG_ONLINE),
            QueryTag::make('Сегодня', fn (Builder $q) => $q->where('last_seen_at', '>=', self::startOfToday()))
                ->alias(self::TAG_TODAY),
            QueryTag::make('За 7 дней', static fn (Builder $q) => $q->where('last_seen_at', '>=', now()->subDays(7)))
                ->alias(self::TAG_WEEK),
            QueryTag::make('За 30 дней', static fn (Builder $q) => $q->where('last_seen_at', '>=', now()->subDays(30)))
                ->alias(self::TAG_MONTH),
        ];
    }

    protected function search(): array
    {
        return ['name', 'email'];
    }

    /**
     * Сутки считаем по времени сайта (Екатеринбург), а в запрос отдаём UTC, как лежит в базе.
     */
    public static function startOfToday(): Carbon
    {
        return now()->setTimezone('Asia/Yekaterinburg')->startOfDay()->utc();
    }

    private function isOnline(User $user): bool
    {
        return $user->last_seen_at?->gte(now()->subMinutes(User::ONLINE_MINUTES)) ?? false;
    }
}
