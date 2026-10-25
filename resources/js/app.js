import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import copyLink from './components/copyLink';
import likeBox from './components/likeBox';
import richEditor from './components/richEditor';

Alpine.plugin(collapse);
Alpine.data('copyLink', copyLink);
Alpine.data('likeBox', likeBox);
Alpine.data('richEditor', richEditor);

window.Alpine = Alpine;
Alpine.start();
