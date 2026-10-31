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
import submitLock from './components/submitLock';
import masonry from './components/masonry';
import bell from './components/bell';
import firmwareFilter from './components/firmwareFilter';
import articleToc from './components/articleToc';
import discussionMessage, { discussionEditor } from './components/discussionMessage';

Alpine.plugin(collapse);
// Показана ли стрелка «вниз» в ленте ответов: стрелка «Наверх» тогда встаёт над ней
Alpine.store('jump', { show: false });
Alpine.data('copyLink', copyLink);
Alpine.data('reactions', reactions);
Alpine.data('photoAttach', photoAttach);
Alpine.data('phoneMask', phoneMask);
Alpine.data('firmwareFilter', firmwareFilter);
Alpine.data('articleToc', articleToc);
Alpine.data('discussionMessage', discussionMessage);
Alpine.data('discussionEditor', discussionEditor);

anchorScroll();
document.addEventListener('DOMContentLoaded', carousel);
document.addEventListener('DOMContentLoaded', lightbox);
document.addEventListener('DOMContentLoaded', scrollRegions);
submitLock();
document.addEventListener('DOMContentLoaded', masonry);
document.addEventListener('DOMContentLoaded', bell);

window.Alpine = Alpine;
Alpine.start();
