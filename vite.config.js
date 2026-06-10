import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/subscriber.css',
                'resources/css/subsciber.css',
                'resources/css/admin.css',
                'resources/js/app.js',
                'resources/js/admin/users.js',
                'resources/js/autopilot.js',
                 'resources/js/react-app.jsx', 
            ],
            refresh: true,
        }),
        vue(),
        react(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
});