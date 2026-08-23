import Alpine from 'alpinejs';
import purchasesApp from './purchases/store.js';

window.Alpine = Alpine;
Alpine.data('purchasesApp', purchasesApp);
Alpine.start();
