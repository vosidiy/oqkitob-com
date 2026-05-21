<template>
 
<div class="container" style="max-width: 450px;">


  <article class="card shadow-lg p-6 mb-8">
  
      <h4 class="text-xl mb-2">Login</h4>
      <p class="text-secondary mb-4">Sign in to continue</p>

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
          <div class="relative d-flex">
              <input
                id="password"
                v-model="form.password"
                :type="isPasswordVisible ? 'text' : 'password'"
                class="form-control"
                placeholder="Enter your password"
                autocomplete="current-password"
              >
              <button
                type="button"
                class="btn absolute text-muted right-0 btn-icon"
                :aria-label="isPasswordVisible ? 'Hide password' : 'Show password'"
                @click="togglePasswordVisibility"
              > <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg> </button>
          </div>
        </div>
        <div class="mb-5">
            <RouterLink :to="{ name: 'forgot-password' }"> Parolni unuttim </RouterLink>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="isSubmitting">
          <span v-if="isSubmitting">Logging in...</span>
          <span v-else>Login</span>
        </button>
      </form>

  </article>

  <RouterLink :to="{ name: 'register' }" class="btn btn-default w-full">  Create account  </RouterLink>

  <p class="text-center text-secondary my-8"> Yordam kerak bo'lsa bog'laning: <br> <a href="https://t.me/websift1990"> Telegram chat </a> </p>
</div> <!-- container .//end -->

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
const isPasswordVisible = ref(false)

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

function togglePasswordVisibility() {
  isPasswordVisible.value = !isPasswordVisible.value
}

</script>
