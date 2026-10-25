// Прокручиваемые таблицы (статьи, каталог прошивок) должны быть доступны с клавиатуры:
// фокус на таблице позволяет листать её стрелками.
export default () =>
    document.querySelectorAll('.article-content table, .data-table').forEach((table, i) => {
        table.setAttribute('tabindex', '0');
        table.setAttribute('role', 'region');
        if (!table.hasAttribute('aria-label')) table.setAttribute('aria-label', `Таблица ${i + 1}, прокручивается стрелками`);
    });
