// Колокольчик в шапке обновляется сам: раз в минуту, пока вкладка открыта, и сразу при возврате на вкладку.
const EVERY = 60_000;

export default () => {
    const bell = document.querySelector('[data-bell]');
    const badge = bell?.querySelector('[data-bell-badge]');
    if (!bell || !badge) return;

    const apply = (unread) => {
        badge.textContent = unread > 99 ? '99+' : String(unread);
        badge.hidden = unread === 0;
        bell.setAttribute('aria-label', 'Уведомления' + (unread ? ': ' + unread + ' новых' : ''));
    };

    const refresh = async () => {
        if (document.hidden) return;
        try {
            const response = await fetch(bell.dataset.url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (response.ok) apply((await response.json()).unread ?? 0);
        } catch (e) { /* нет сети: попробуем в следующий раз */ }
    };

    setInterval(refresh, EVERY);
    document.addEventListener('visibilitychange', refresh);
};
