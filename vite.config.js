import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',      
                'resources/js/login.js',
                'resources/js/folders.js',
                'resources/js/timeline.js',
                'resources/js/albums.js',
                'resources/js/album.js',
                'resources/js/users.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
