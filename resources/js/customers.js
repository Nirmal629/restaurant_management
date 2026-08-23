import Alpine from 'alpinejs';
import customersApp from './customers/store.js';

window.Alpine = Alpine;
Alpine.data('customersApp', customersApp);
Alpine.start();
