import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { authStore } from '@/stores/auth'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.mount('#app')

authStore.checkAuth()
