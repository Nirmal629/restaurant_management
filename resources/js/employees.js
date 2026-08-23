import Alpine from 'alpinejs';
import employeesApp from './employees/store.js';

window.Alpine = Alpine;
Alpine.data('employeesApp', employeesApp);
Alpine.start();
