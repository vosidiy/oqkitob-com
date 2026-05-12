import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { authStore } from '@/stores/auth'

createApp(App).use(router).mount('#app')

authStore.checkAuth()
