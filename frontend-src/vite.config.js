import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
// import { viteSingleFile } from 'vite-plugin-singlefile' // optional to keep js in html

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
   //  viteSingleFile(), // optional to keep js in html
  ],
  base: '/',
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    outDir: '../dist', 
    emptyOutDir: true,
    // assetsDir: 'assets',   // ← hashed JS/CSS goes into root assets/
  },
  server: {
    proxy: {
      // Shorthand: any request starting with "/api" 
      // will be sent to the PHP server
      '/api': {
        target: 'http://localhost:8888',
        changeOrigin: true,
        secure: false,
      }
    }
  },
})
