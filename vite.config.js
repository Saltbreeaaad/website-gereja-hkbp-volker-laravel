import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

/**
 * Pisahkan pustaka pihak ketiga dari kode aplikasi supaya cache browser tidak
 * ikut hangus setiap kali app.js diubah. Rolldown (Vite 8) hanya menerima
 * manualChunks dalam bentuk fungsi.
 */
function manualChunks(id) {
    if (!id.includes('node_modules')) {
        return undefined;
    }

    if (id.includes('swiper')) return 'vendor-swiper';
    if (id.includes('chart.js') || id.includes('@kurkle')) return 'vendor-chart';
    if (id.includes('lucide')) return 'vendor-icons';

    return undefined;
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: { manualChunks },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
