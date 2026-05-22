<template>
 
<div class="container" style="max-width: 450px;">


  <article class="card shadow-lg p-6 mb-8">
  
      <h4 class="text-xl mb-2">{{ $t('auth.login.title') }}</h4>
      <p class="text-secondary mb-4">{{ $t('auth.login.subtitle') }}</p>

      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-3">
          <label class="form-label" for="email">{{ $t('common.fields.email') }}</label>
          <input
            id="email"
            v-model.trim="form.email"
            type="email"
            class="form-control"
            placeholder="example@mail.com"
            autocomplete="email"
          >
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">{{ $t('common.fields.password') }}</label>
          <div class="relative d-flex">
              <input
                id="password"
                v-model="form.password"
                :type="isPasswordVisible ? 'text' : 'password'"
                class="form-control"
                placeholder=""
                autocomplete="current-password"
              >
              <button
                type="button"
                class="btn absolute text-muted right-0 btn-icon"
                :aria-label="isPasswordVisible ? $t('auth.login.hidePassword') : $t('auth.login.showPassword')"
                @click="togglePasswordVisibility"
              > <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg> </button>
          </div>
        </div>
        <div class="mb-5">
            <RouterLink :to="{ name: 'forgot-password' }">{{ $t('auth.login.forgotPassword') }}</RouterLink>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="isSubmitting">
          <span v-if="isSubmitting">{{ $t('common.states.loading') }}</span>
          <span v-else>{{ $t('auth.login.submit') }}</span>
        </button>
      </form>

  </article>

  <RouterLink :to="{ name: 'register' }" class="btn btn-default w-full">{{ $t('auth.login.createAccount') }}</RouterLink>

  <p class="text-center text-secondary my-8">{{ $t('auth.login.helpText') }}<br> <a href="https://t.me/websift1990">{{ $t('auth.login.telegramChat') }}</a> </p>
</div> <!-- container .//end -->

</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage } from '@/api/errors'
import { authStore } from '@/stores/auth'

const router = useRouter()
const { t } = useI18n()

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
  return getApiErrorMessage(error, t('auth.login.unableToSignIn'))
}

function togglePasswordVisibility() {
  isPasswordVisible.value = !isPasswordVisible.value
}

</script>
