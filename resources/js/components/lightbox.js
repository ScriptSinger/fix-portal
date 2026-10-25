// Окно с увеличенной картинкой (в старой теме это был Bootstrap-модал #imageModal):
// клик по картинке внутри статьи/вопроса/ответа или по превью в списке открывает её в окне.
export default () => {
    const root = document.getElementById('lightbox');
    if (!root) return;
    const img = root.querySelector('img');
    const closeButton = root.querySelector('button');
    let opener = null;
    const close = () => {
        root.classList.add('hidden'); root.classList.remove('flex'); img.removeAttribute('src'); document.body.classList.remove('overflow-hidden');
        opener?.focus(); opener = null;
    };
    const open = (src, alt) => {
        opener = document.activeElement;
        img.src = src; img.alt = alt || ''; root.classList.remove('hidden'); root.classList.add('flex'); document.body.classList.add('overflow-hidden'); closeButton.focus();
    };

    // role="button" для картинок в тексте, как в старой теме; с клавиатуры открываются Enter и пробелом
    document.querySelectorAll('.article-content img, [data-lightbox-cover]').forEach((el) => {
        el.setAttribute('role', 'button');
        if (!el.closest('a[href]')) el.setAttribute('tabindex', '0');
    });

    document.addEventListener('click', (event) => {
        if (!root.classList.contains('hidden')) {
            if (event.target === root || event.target.closest('[data-lightbox-close]')) close();
            return;
        }
        const trigger = event.target.closest('[data-lightbox]');
        if (trigger) {
            event.preventDefault();
            open(trigger.dataset.lightbox, trigger.querySelector('img')?.alt);
            return;
        }
        const image = event.target.closest('.article-content img, [data-lightbox-cover]');
        if (!image) return;
        const link = image.closest('a');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) return; // реальная ссылка
        event.preventDefault();
        open(image.currentSrc || image.src, image.alt);
    });
    document.addEventListener('keydown', (event) => {
        if (root.classList.contains('hidden')) {
            const image = event.target.closest?.('.article-content img[tabindex], [data-lightbox-cover][tabindex]');
            if (image && (event.key === 'Enter' || event.key === ' ')) { event.preventDefault(); open(image.currentSrc || image.src, image.alt); }
            return;
        }
        if (event.key === 'Escape') close();
        if (event.key === 'Tab') { event.preventDefault(); closeButton.focus(); } // в окне один элемент управления: фокус не уходит под затемнение
    });
};
