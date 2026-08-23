import Alpine from 'alpinejs';
import inventoryApp from './inventory/store.js';

window.Alpine = Alpine;
Alpine.data('inventoryApp', inventoryApp);
Alpine.start();
