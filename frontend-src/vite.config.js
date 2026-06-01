import { fileURLToPath, URL } from 'node:url'
import { existsSync, renameSync, rmSync } from 'node:fs'
import { resolve } from 'node:path'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
// import vueDevTools from 'vite-plugin-vue-devtools'
// import { viteSingleFile } from 'vite-plugin-singlefile' // optional to keep js in html

function renameIndexHtmlToAppHtml() {
  let outDir

  return {
    name: 'rename-index-html-to-app-html',
    apply: 'build',
    configResolved(config) {
      outDir = resolve(config.root, config.build.outDir)
    },
    closeBundle() {
      const indexHtmlPath = resolve(outDir, 'index.html')
      const appHtmlPath = resolve(outDir, 'app.html')

      if (!existsSync(indexHtmlPath)) {
        return
      }

      rmSync(appHtmlPath, { force: true })
      renameSync(indexHtmlPath, appHtmlPath)
    },
  }
}

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    // vueDevTools(),
    renameIndexHtmlToAppHtml(),
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
