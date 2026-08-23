import Alpine from 'alpinejs';
import expensesApp from './expenses/store.js';

window.Alpine = Alpine;
Alpine.data('expensesApp', expensesApp);
Alpine.start();
