import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import pathModule from 'path';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: [
                'resources/web/js/app.js',
                'resources/admin/scss/app.scss',
                'resources/web/css/style.scss',
                'resources/admin/scss/pages/page-auth.scss',

                'resources/admin/js/app.js',
                'resources/admin/js/pages/users.js',
                'resources/admin/js/pages/admins.js',
                'resources/admin/js/pages/trophies.js',
                'resources/admin/js/pages/balance.js',
                'resources/admin/js/pages/exchanges.js',
                'resources/admin/js/pages/items.js',
                'resources/admin/js/pages/chests.js',
                'resources/admin/js/pages/keys.js',
                'resources/admin/js/pages/assignment-of-trophies.js',
                'resources/views/api/mail/css/styles.css'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output:{
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return id.toString().split('node_modules/')[1].split('/')[0].toString();
                    }
                }
            }
        }
    },
    resolve: {
        alias: {
            '~bootstrap': pathModule.resolve(__dirname, 'node_modules/bootstrap'),
        }
    },
});
