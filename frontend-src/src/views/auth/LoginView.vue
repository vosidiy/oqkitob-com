<template>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="text-3xl font-bold mb-2">Login</h1>
      <p class="text-secondary mb-4">Sign in to continue to your books dashboard.</p>

      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-3">
          <label class="form-label" for="email">Email address</label>
          <input
            id="email"
            v-model.trim="form.email"
            type="email"
            class="form-control"
            placeholder="you@example.com"
            autocomplete="email"
          >
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            placeholder="Enter your password"
            autocomplete="current-password"
          >
        </div>

        <div class="small text-secondary mb-4">
          Demo password for seeded users: <span class="font-semibold">Demo123!</span>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="isSubmitting">
          <span v-if="isSubmitting">Logging in...</span>
          <span v-else>Login</span>
        </button>
      </form>

      <div class="d-flex justify-content-between gap-3 mt-4 small">
        <RouterLink :to="{ name: 'forgot-password' }">Forgot password?</RouterLink>
        <RouterLink :to="{ name: 'register' }">Create account</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getApiErrorMessage } from '@/api/errors'
import { authStore } from '@/stores/auth'

const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const errorMessage = ref('')
const isSubmitting = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await authStore.login({
      email: form.email,
      password: form.password,
    })

    router.push({ name: 'dashboard-home' })
  } catch (error) {
    errorMessage.value = getLoginErrorMessage(error)
  } finally {
    isSubmitting.value = false
  }
}

function getLoginErrorMessage(error) {
  return getApiErrorMessage(error, 'Unable to sign in right now.')
}
</script>
