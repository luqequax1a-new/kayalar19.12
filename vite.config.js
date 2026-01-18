import { glob } from "glob";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import copy from "rollup-plugin-copy";
import path from "path";
import autoprefixer from "autoprefixer";
import postcssRTLCSS from "postcss-rtlcss";

// FleetCart version
const VERSION = "4.7.11";

export default defineConfig(async ({ command }) => {
    const isDev = command === 'serve';

    // Glob pattern for assets - MORE SPECIFIC for faster scanning
    const assetPatterns = [
        "modules/*/Resources/assets/**/app.scss",
        "modules/*/Resources/assets/**/app.js",
        "modules/*/Resources/assets/**/main.scss",
        "modules/*/Resources/assets/**/main.js",
        "modules/*/Resources/assets/**/create.js",
        "modules/*/Resources/assets/**/edit.js",
        "modules/Storefront/Resources/assets/public/sass/vendors/*.scss",
    ];

    // Fetching the asset files asynchronously
    const assets = await glob(assetPatterns, {
        ignore: ["node_modules/**", "vendor/**", "storage/**", "public/**"],
        absolute: false,
    });

    return {
        base: "",

        // OPTIMIZATION: Pre-bundle dependencies
        optimizeDeps: {
            include: [
                'vue',
                'jquery',
                'axios',
                'lodash',
                'bootstrap',
                'alpinejs',
                '@popperjs/core',
            ],
            exclude: ['tinymce'], // Large packages
            force: false, // Don't force re-bundle on every start
        },

        server: {
            host: process.env.HOST || "localhost",
            port: Number(process.env.PORT) || Number(process.env.VITE_PORT) || 5173,
            strictPort: true,
            origin: process.env.VITE_DEV_SERVER_ORIGIN || undefined,

            // OPTIMIZATION: Faster file watching
            watch: {
                usePolling: false,
                interval: 100,
                ignored: ['**/node_modules/**', '**/vendor/**', '**/storage/**'],
            },

            hmr: {
                host: process.env.VITE_HMR_HOST || process.env.HOST || "localhost",
                protocol: process.env.VITE_HMR_PROTOCOL || undefined,
                port:
                    Number(process.env.VITE_HMR_PORT)
                    || Number(process.env.PORT)
                    || Number(process.env.VITE_PORT)
                    || 5173,
                overlay: true,
            },

            // OPTIMIZATION: Faster middleware
            middlewareMode: false,
            fs: {
                strict: false,
            },
        },
        plugins: [
            laravel({
                input: [
                    "modules/Admin/Resources/assets/sass/dashboard.scss",
                    "modules/Admin/Resources/assets/js/dashboard.js",
                    "modules/Order/Resources/assets/admin/sass/print.scss",
                    "modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js",
                    ...assets,
                ],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            // OPTIMIZATION: Only copy files during build, not dev
            ...(isDev ? [] : [
                copy({
                    targets: [
                        {
                            src: [
                                "public/favicon.ico",
                                "node_modules/jquery/dist/jquery.min.js",
                                "node_modules/tinymce",
                                "node_modules/selectize/dist/js/standalone/selectize.min.js",
                                "node_modules/jstree/dist/jstree.min.js",
                                "modules/Admin/Resources/assets/images/*",
                                "modules/Admin/Resources/assets/vendors/js/bootstrap.min.js",
                                "modules/Storefront/Resources/assets/public/images/*",
                            ],
                            dest: "public/build/assets",
                        },
                        {
                            src: "modules/Admin/Resources/assets/fonts",
                            dest: "public/build",
                        },
                        {
                            src: "node_modules/line-awesome/dist/line-awesome/fonts",
                            dest: "modules/Storefront/Resources/assets/public",
                        },
                    ],
                    copyOnce: true,
                    hook: "writeBundle",
                }),
            ]),
        ],
        css: {
            devSourcemap: false,
            postcss: {
                plugins: [
                    autoprefixer(),
                    postcssRTLCSS({
                        ltrPrefix: ".ltr",
                        rtlPrefix: ".rtl",
                        processKeyFrames: true,
                    }),
                ],
            },
        },
        resolve: {
            alias: {
                vue: path.resolve(
                    __dirname,
                    "./node_modules/vue/dist/vue.esm-bundler.js"
                ),
                "@modules": path.resolve(__dirname, "./modules"),
                "@admin": path.resolve(
                    __dirname,
                    "./modules/Admin/Resources/assets"
                ),
            },
        },
        build: {
            sourcemap: false,
            rollupOptions: {
                output: {
                    manualChunks(id) {
                        if (id.includes("node_modules")) {
                            return id.split("node_modules/")[1].split("/")[0];
                        }
                    },
                    entryFileNames: `assets/[name]-[hash]-v${VERSION}.js`,
                    chunkFileNames: `assets/[name]-[hash]-v${VERSION}.js`,
                    assetFileNames: `assets/[name]-[hash]-v${VERSION}.[ext]`,
                },
            },
        },
        esbuild: { legalComments: "none" },
    };
});
