@props([
    'resetUrl',
])

<div class="filter-actions-right">
    <button type="submit" class="btn btn-primary filter-action-btn">Применить</button>
    <a href="{{ $resetUrl }}" class="btn btn-primary filter-action-btn filter-action-reset">Сбросить</a>
</div>
