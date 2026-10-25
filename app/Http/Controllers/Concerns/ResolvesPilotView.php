<?php

namespace App\Http\Controllers\Concerns;

/**
 * Пилот перехода на Vite + Tailwind: маршруты pilot.* рисуются шаблонами tw.*,
 * боевые маршруты продолжают использовать public.*. После переключения трейт удаляется.
 */
trait ResolvesPilotView
{
    protected function isPilot(): bool
    {
        return request()->routeIs('pilot.*');
    }

    protected function pilotView(string $name): string
    {
        return ($this->isPilot() ? 'tw.' : 'public.') . $name;
    }
}
