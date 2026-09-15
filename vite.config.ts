import { resolve } from 'node:path';
import { defineConfig } from 'vite-plus';
import dts from 'vite-plugin-dts';

export default defineConfig({
    build: {
        lib: {
            entry: resolve(import.meta.dirname, 'resources/js/index.ts'),
            formats: ['es', 'cjs', 'iife'],
            name: 'Mosaicast',
            fileName: (format) => {
                if (format === 'cjs') {
                    return 'index.common.js';
                }

                return format === 'es' ? 'index.js' : 'mosaicast.iife.js';
            },
        },
        rollupOptions: {
            external: ['@inertiajs/vue3', 'vue'],
            output: {
                globals: {
                    '@inertiajs/vue3': 'Inertia',
                },
            },
        },
    },
    plugins: [dts({ include: ['resources/js/index.ts'] })],
    fmt: {
        ignorePatterns: [
            '.agents/**',
            '.github/**',
            'CHANGELOG.md',
            'README.md',
            'TODO.md',
            'composer.json',
            'config/**',
            'database/**',
            'dist/**',
            'lang/**',
            'pint.json',
            'public/**',
            'resources/boost/**',
            'routes/**',
            'src/**',
            'tests/**',
            'workbench/**',
        ],
        semi: true,
        singleQuote: true,
    },
    lint: {
        ignorePatterns: ['dist/**'],
        options: {
            denyWarnings: true,
            typeAware: true,
        },
    },
    test: {
        include: ['resources/js/**/*.test.ts'],
    },
});
