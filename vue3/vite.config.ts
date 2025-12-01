import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import UnoCSS from "unocss/vite";

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue(), UnoCSS()],
    server: {
        proxy: {
            "/api": {
                target: "http://localhost:8080", // Backend URL
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, ""),
            },
        },
    },
});
