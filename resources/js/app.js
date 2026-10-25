import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import copyLink from './components/copyLink';
import likeBox from './components/likeBox';
import reactions from './components/reactions';
import richEditor from './components/richEditor';
import avatarUpload from './components/avatarUpload';
import phoneMask from './components/phoneMask';
import anchorScroll from './components/anchorScroll';
import carousel from './components/carousel';
import lightbox from './components/lightbox';
import scrollRegions from './components/scrollRegions';
import firmwareFilter from './components/firmwareFilter';
import articleToc from './components/articleToc';

Alpine.plugin(collapse);
Alpine.data('copyLink', copyLink);
Alpine.data('likeBox', likeBox);
Alpine.data('reactions', reactions);
Alpine.data('richEditor', richEditor);
Alpine.data('avatarUpload', avatarUpload);
Alpine.data('phoneMask', phoneMask);
Alpine.data('firmwareFilter', firmwareFilter);
Alpine.data('articleToc', articleToc);

anchorScroll();
document.addEventListener('DOMContentLoaded', carousel);
document.addEventListener('DOMContentLoaded', lightbox);
document.addEventListener('DOMContentLoaded', scrollRegions);

window.Alpine = Alpine;
Alpine.start();
