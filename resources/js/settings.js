import Alpine from 'alpinejs';
import settingsApp from './settings/store.js';

window.Alpine = Alpine;
Alpine.data('settingsApp', settingsApp);
Alpine.start();
