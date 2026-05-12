import { reactive } from 'vue'
import { api } from '@/services/api'

const state = reactive({
  user: null,
  checked: false,
  checkPromise: null,
})

let hasValidatedServer = false

export const authStore = {
  state,

  async checkAuth() {
    if (state.checkPromise) {
      return state.checkPromise
    }

    state.checkPromise = (async () => {
      try {
        const { data } = await api.get('/auth/me')
        state.user = data.user

        return state.user
      } catch (error) {
        state.user = null

        return null
      } finally {
        state.checked = true
        hasValidatedServer = true
        state.checkPromise = null
      }
    })()

    return state.checkPromise
  },

  async ensureChecked() {
    if (hasValidatedServer) {
      return state.user
    }

    return this.checkAuth()
  },

  async hydrateUser() {
    return this.checkAuth()
  },

  async login(payload) {
    const { data } = await api.post('/auth/login', payload)
    state.user = data.user
    state.checked = true
    hasValidatedServer = true

    return data
  },

  async logout() {
    await api.post('/auth/logout')

    state.user = null
    state.checked = true
    hasValidatedServer = true
  },
}
