import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    // usePolling: true 是關鍵，Windows + Docker 環境需要這個才能偵測到檔案變更
    watch: {
      usePolling: true
    }
  }
})