<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\DateRange;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[Icon('users')]
#[Group('Пользователи', 'user-group')]
#[Order(7)]
/**
 * Пользователи сайта (не администраторы админки).
 *
 * @extends ModelResource<User>
 */
class UserResource extends BaseResource
{
    protected string $model = User::class;

    protected string $column = 'name';

    protected int $itemsPerPage = 50;

    protected string $sortColumn = 'id';

    public function getTitle(): string
    {
        return 'Пользователи сайта';
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Имя', 'name')->sortable(),
            Email::make('E-mail', 'email')->sortable(),
            Text::make('Локация', 'location'),
            Date::make('Подтверждён', 'email_verified_at')->format('d.m.Y')->sortable(),
            Date::make('Регистрация', 'created_at')->format('d.m.Y')->sortable(),
            Date::make('Последний визит', 'last_seen_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ...$this->indexFields(),
            Text::make('Телефон', 'phone'),
            Textarea::make('О себе', 'bio'),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Имя', 'name')->required(),
            Email::make('E-mail', 'email')->required(),
            Phone::make('Телефон', 'phone'),
            Text::make('Локация', 'location'),
            Textarea::make('О себе', 'bio'),
            Password::make('Пароль', 'password')
                ->customAttributes(['autocomplete' => 'new-password'])
                ->eye()
                ->onApply(static fn ($item, $value) => filled($value)
                    ? tap($item, static fn ($i) => $i->password = Hash::make($value))
                    : $item),
        ];
    }

    protected function rules($item): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($item?->getKey())],
            'phone' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:255'],
            'password' => [$item?->exists ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    protected function filters(): iterable
    {
        return [
            Text::make('Имя', 'name')
                ->onApply(fn (Builder $query, $value) => $value === null || $value === '' ? $query : $query->where('name', 'like', '%' . self::escapeLike((string) $value) . '%')),
            Text::make('E-mail', 'email')
                ->onApply(fn (Builder $query, $value) => $value === null || $value === '' ? $query : $query->where('email', 'like', '%' . self::escapeLike((string) $value) . '%')),
            Select::make('Почта', 'email_state')
                ->options(['verified' => 'Подтверждена', 'unverified' => 'Не подтверждена'])
                ->nullable()
                ->onApply(fn (Builder $query, $value) => match ($value) {
                    'verified' => $query->whereNotNull('email_verified_at'),
                    'unverified' => $query->whereNull('email_verified_at'),
                    default => $query,
                }),
            DateRange::make('Регистрация', 'created_at'),
            DateRange::make('Последний визит', 'last_seen_at'),
        ];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make('Подтверждённые', static fn (Builder $q) => $q->whereNotNull('email_verified_at'))->alias('verified'),
            QueryTag::make('Не подтверждённые', static fn (Builder $q) => $q->whereNull('email_verified_at'))->alias('unverified'),
            QueryTag::make('Удалённые', static fn (Builder $q) => $q->onlyTrashed())->alias('trashed'),
        ];
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    protected function search(): array
    {
        return ['id', 'name', 'email'];
    }
}
