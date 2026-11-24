import path from 'node:path';

import { defineConfig } from 'vite';

export default defineConfig({
    root: './src/web/assets',
    // CP bundles publish under Craft's cpresources, so a relative base keeps lazy chunks beside the entry.
    base: '',

    build: {
        outDir: 'cp/dist',
        emptyOutDir: true,
        manifest: 'manifest.json',
        // The dev server maps sources on its own. Built bundles ship without maps.
        sourcemap: false,
        rolldownOptions: {
            input: {
                shield: '/cp/src/js/shield.ts',
            },
        },
    },

    server: {
        origin: 'http://localhost:4046',
        hmr: {
            protocol: 'ws',
        },
    },

    resolve: {
        alias: {
            '@': path.resolve('./src/web/assets/cp/src'),
        },
        preserveSymlinks: false,
    },
});
