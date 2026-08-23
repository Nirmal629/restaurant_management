import Alpine from 'alpinejs';
import menuApp from './menu/store.js';

window.Alpine = Alpine;
Alpine.data('menuApp', menuApp);
Alpine.start();
