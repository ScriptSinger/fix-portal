import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

import copyLink from './components/copyLink';
import likeBox from './components/likeBox';
import richEditor from './components/richEditor';
import avatarUpload from './components/avatarUpload';
import phoneMask from './components/phoneMask';

Alpine.plugin(collapse);
Alpine.data('copyLink', copyLink);
Alpine.data('likeBox', likeBox);
Alpine.data('richEditor', richEditor);
Alpine.data('avatarUpload', avatarUpload);
Alpine.data('phoneMask', phoneMask);

window.Alpine = Alpine;
Alpine.start();
