import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css' ,
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // Inside Docker the dev server listens on 0.0.0.0; the browser must still use localhost
    server: { hmr: { host: 'localhost' } },
});
