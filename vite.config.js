import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
    host: '0.0.0.0',       // listen semua interface
    hmr: {
        host: 'amora-local', // ganti ke hostname PC-mu
    },
    port: 5173,
},
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
