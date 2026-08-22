import Alpine from 'alpinejs';
import tablesApp from './tables/store.js';

window.Alpine = Alpine;
Alpine.data('tablesApp', tablesApp);
Alpine.start();
