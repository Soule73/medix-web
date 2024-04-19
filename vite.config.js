import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/filament/doctor/theme.css"
                // 'resources/css/filament.css'
            ],
            // refresh: true,
            refresh: [
                'resources/routes/**',
                'routes/**',
                'app/**',
                'app/Filament/**/**/**/**/**',
                'resources/views/**',

            ],
        }),
    ],
});
