import Alpine from 'alpinejs';
import billingApp from './billing/store.js';

window.Alpine = Alpine;
Alpine.data('billingApp', billingApp);
Alpine.start();
