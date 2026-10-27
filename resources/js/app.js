import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import copyLink from './components/copyLink';
import reactions from './components/reactions';
import photoAttach from './components/photoAttach';
import phoneMask from './components/phoneMask';
import anchorScroll from './components/anchorScroll';
import carousel from './components/carousel';
import lightbox from './components/lightbox';
import scrollRegions from './components/scrollRegions';
import firmwareFilter from './components/firmwareFilter';
import articleToc from './components/articleToc';

Alpine.plugin(collapse);
// Показана ли стрелка «вниз» в ленте ответов: стрелка «Наверх» тогда встаёт над ней
Alpine.store('jump', { show: false });
Alpine.data('copyLink', copyLink);
Alpine.data('reactions', reactions);
Alpine.data('photoAttach', photoAttach);
Alpine.data('phoneMask', phoneMask);
Alpine.data('firmwareFilter', firmwareFilter);
Alpine.data('articleToc', articleToc);

anchorScroll();
document.addEventListener('DOMContentLoaded', carousel);
document.addEventListener('DOMContentLoaded', lightbox);
document.addEventListener('DOMContentLoaded', scrollRegions);

window.Alpine = Alpine;
Alpine.start();
