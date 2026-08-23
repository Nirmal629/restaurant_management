import Alpine from 'alpinejs';
import reservationsApp from './reservations/store.js';

window.Alpine = Alpine;
Alpine.data('reservationsApp', reservationsApp);
Alpine.start();
