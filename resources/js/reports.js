import Alpine from 'alpinejs';
import reportsApp from './reports/store.js';

window.Alpine = Alpine;
Alpine.data('reportsApp', reportsApp);
Alpine.start();
