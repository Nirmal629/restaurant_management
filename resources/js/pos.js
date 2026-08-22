import Alpine from 'alpinejs';
import posApp from './pos/store.js';

window.Alpine = Alpine;
Alpine.data('posApp', posApp);
Alpine.start();
