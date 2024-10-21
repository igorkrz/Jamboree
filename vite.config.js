import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import reactPlugin from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        reactPlugin(),
        symfonyPlugin({
            stimulus: true,
        }),
    ],
    css: {
        postcss: './postcss.config.js',
    },
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.jsx"
            },
        }
    },
});
