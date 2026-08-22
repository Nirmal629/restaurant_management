import Alpine from 'alpinejs';
import kdsApp from './kds/store.js';

window.Alpine = Alpine;
Alpine.data('kdsApp', kdsApp);
Alpine.start();
