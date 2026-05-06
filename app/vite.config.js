import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '127.0.0.1', // 👈 ВАЖНО: убирает [::1]
        port: 5173,
        strictPort: true,

        watch: {
            ignored: ['**/storage/framework/views/**'],
        },

        hmr: {
            host: 'localhost', // 👈 фикс для hot reload
        },
    },
});
