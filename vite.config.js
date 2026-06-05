import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,

        hmr: {
            host: '192.168.1.142',
            protocol: 'ws',
            port: 5173,
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/auth.css',
                'resources/js/app.js',
                'resources/js/auth.js',
                'resources/js/sppd-form.js',
                'resources/js/bbm-form.js',
                'resources/js/vehicle-usage-log.js',
            ],
            refresh: true,
        }),
    ],
});
