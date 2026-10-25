import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import copyLink from './components/copyLink';
import likeBox from './components/likeBox';

Alpine.plugin(collapse);
Alpine.data('copyLink', copyLink);
Alpine.data('likeBox', likeBox);

window.Alpine = Alpine;
Alpine.start();
