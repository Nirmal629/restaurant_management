import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/pos.css',
                'resources/js/pos.js',
                'resources/css/tables.css',
                'resources/js/tables.js',
                'resources/css/kds.css',
                'resources/js/kds.js',
                'resources/css/billing.css',
                'resources/js/billing.js',
                'resources/css/admin.css',
                'resources/js/reservations.js',
                'resources/js/customers.js',
                'resources/js/menu.js',
                'resources/js/inventory.js',
                'resources/js/purchases.js',
                'resources/js/expenses.js',
                'resources/js/reports.js',
                'resources/js/orders.js',
                'resources/js/employees.js',
                'resources/js/settings.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
