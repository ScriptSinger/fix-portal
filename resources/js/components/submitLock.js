// Защита от двойной отправки и обратная связь: после отправки формы кнопки блокируются и показывают «занято»,
// повторный Enter или клик не отправит форму ещё раз. Поиск и фильтры (GET) не трогаем.
export default () => {
    const unlock = (form) => {
        delete form.dataset.sending;
        form.querySelectorAll('[data-sending]').forEach((b) => { b.disabled = false; b.removeAttribute('data-sending'); b.removeAttribute('aria-busy'); });
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.method.toLowerCase() === 'get' || form.hasAttribute('data-no-lock')) return;
        if (form.dataset.sending) { event.preventDefault(); return; }

        // после остальных обработчиков (подтверждение удаления, проверка полей) и после того, как браузер собрал данные формы
        setTimeout(() => {
            if (event.defaultPrevented) return;
            form.dataset.sending = '1';
            form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach((b) => {
                b.disabled = true; b.setAttribute('data-sending', ''); b.setAttribute('aria-busy', 'true');
            });
        }, 0);
    });

    // «Назад» из кэша браузера возвращает страницу с заблокированными кнопками: снимаем блокировку
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) document.querySelectorAll('form[data-sending]').forEach(unlock);
    });
};
