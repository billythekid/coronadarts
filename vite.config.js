import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  base: '/dist/',
  plugins: [vue()],
  build: {
    outDir: path.resolve(__dirname, 'web/dist'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'assets/js/app.js'),
      external: [/^\/assets\//],
    },
  },
  define: {
    __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: 'false',
  },
});
