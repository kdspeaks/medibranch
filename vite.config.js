// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  server: {
    host: '0.0.0.0',   // ✅ allows Vite to be accessed from outside
    port: 5173,
    hmr: {
      host: 'medibranch.local',  // ✅ your domain (must match hosts file)
    },
  },
  plugins: [
    laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
  ],
});

