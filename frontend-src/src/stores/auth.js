import { reactive } from 'vue'
import { fetchCurrentUserRequest, loginRequest, logoutRequest, registerRequest } from '@/api/auth'

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
        const { data } = await fetchCurrentUserRequest()
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
    // After the first server-backed auth check, reuse the cached result for
    // router guards and views instead of calling /auth/me again.
    if (hasValidatedServer) {
      return state.user
    }

    return this.checkAuth()
  },

  async login(payload) {
    const { data } = await loginRequest(payload)
    state.user = data.user
    state.checked = true
    hasValidatedServer = true

    return data
  },

  async register(payload) {
    const { data } = await registerRequest(payload)
    state.user = data.user
    state.checked = true
    hasValidatedServer = true

    return data
  },

  async logout() {
    await logoutRequest()

    state.user = null
    state.checked = true
    hasValidatedServer = true
  },
}
