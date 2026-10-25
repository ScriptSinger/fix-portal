// Окно с увеличенной картинкой (в старой теме это был Bootstrap-модал #imageModal):
// клик по картинке внутри статьи/вопроса/ответа или по превью в списке открывает её в окне.
export default () => {
    const root = document.getElementById('lightbox');
    if (!root) return;
    const img = root.querySelector('img');
    const close = () => { root.classList.add('hidden'); root.classList.remove('flex'); img.removeAttribute('src'); document.body.classList.remove('overflow-hidden'); };
    const open = (src, alt) => { img.src = src; img.alt = alt || ''; root.classList.remove('hidden'); root.classList.add('flex'); document.body.classList.add('overflow-hidden'); root.querySelector('button').focus(); };

    // role="button" для картинок в тексте, как в старой теме
    document.querySelectorAll('.article-content img, [data-lightbox-cover]').forEach((el) => el.setAttribute('role', 'button'));

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
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !root.classList.contains('hidden')) close(); });
};
