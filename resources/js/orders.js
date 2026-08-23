import Alpine from 'alpinejs';
import ordersApp from './orders/store.js';

Alpine.data('ordersApp', ordersApp);

Alpine.start();
