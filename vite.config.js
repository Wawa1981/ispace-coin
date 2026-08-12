import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            // Refresh ciblé — évite de watcher tout le monorepo / vendor
            refresh: [
                'resources/views/**',
                'resources/js/**',
                'routes/**',
                'app/Http/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    // IPv4 only — [::1] casse le hot reload dans beaucoup de navigateurs
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            // Ne pas watcher vendor / storage / etc. (évite ENOSPC inotify)
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',
                '**/public/build/**',
                '**/.git/**',
            ],
        },
    },
});
