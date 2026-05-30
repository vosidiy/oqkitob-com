<template>
<div class="container" style="max-width: 450px;">

  <article class="card shadow-lg p-6 mb-8">
    <h4 class="text-2xl font-bold mb-2">{{ $t('auth.register.title') }}</h4>
    <p class="text-secondary mb-4">{{ $t('auth.register.subtitle') }}</p>

    <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="handleSubmit">
      <div class="mb-3">
        <label class="form-label" for="register-phone">{{ $t('common.fields.phone') }}</label>
        <input
          id="register-phone"
          v-model.trim="form.phone"
          type="tel"
          class="form-control"
          :placeholder="$t('auth.register.phonePlaceholder')"
          autocomplete="tel"
        >
      </div>

      <div class="mb-3">
        <label class="form-label" for="register-name">{{ $t('auth.register.nameLabel') }}</label>
        <input
          id="register-name"
          v-model.trim="form.name"
          type="text"
          class="form-control"
          :placeholder="$t('auth.register.namePlaceholder')"
          autocomplete="name"
        >
      </div>

      <div class="mb-3">
        <label class="form-label" for="register-password">{{ $t('common.fields.password') }}</label>
        <div class="relative d-flex">
          <input
            id="register-password"
            v-model="form.password"
            :type="isPasswordVisible ? 'text' : 'password'"
            class="form-control"
            autocomplete="new-password"
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
        <label class="form-label" for="register-password-confirmation">{{ $t('auth.register.repeatPassword') }}</label>
        <div class="relative d-flex">
          <input
            id="register-password-confirmation"
            v-model="form.password_confirmation"
            :type="isPasswordVisible ? 'text' : 'password'"
            class="form-control"
            autocomplete="new-password"
          >
          <button
            type="button"
            class="btn absolute text-muted right-0 btn-icon"
            :aria-label="isPasswordVisible ? $t('auth.login.hidePassword') : $t('auth.login.showPassword')"
            @click="togglePasswordVisibility"
          > <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg> </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="isSubmitting">
        <span v-if="isSubmitting">{{ $t('common.states.creating') }}</span>
        <span v-else>{{ $t('auth.register.submit') }}</span>
      </button>
    </form>
  </article>

  <RouterLink class="btn btn-default w-full" :to="{ name: 'login' }">
    {{ $t('auth.register.backToLogin') }}
  </RouterLink>

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
  phone: '+998',
  name: '',
  password: '',
  password_confirmation: '',
})

const REGISTER_PASSWORD_MIN_LENGTH = 5
const errorMessage = ref('')
const isSubmitting = ref(false)
const isPasswordVisible = ref(false)

async function handleSubmit() {
  const validationMessage = validateRegisterForm()

  if (validationMessage !== '') {
    window.alert(validationMessage)
    return
  }

  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await authStore.register({
      phone: form.phone,
      name: form.name,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    router.push({ name: 'dashboard-home' })
  } catch (error) {
    errorMessage.value = getApiErrorMessage(error, t('auth.register.unableToRegister'))
  } finally {
    isSubmitting.value = false
  }
}

function togglePasswordVisibility() {
  isPasswordVisible.value = !isPasswordVisible.value
}

function validateRegisterForm() {
  const trimmedName = form.name.trim()
  const trimmedPhone = form.phone.trim()

  if (! isValidInternationalPhone(trimmedPhone)) {
    return t('auth.register.validationPhoneInvalid')
  }

  if (trimmedName.length < 2) {
    return t('auth.register.validationNameTooShort')
  }

  if (looksLikeGibberish(trimmedName)) {
    return t('auth.register.validationNameGibberish')
  }

  if (form.password.length < REGISTER_PASSWORD_MIN_LENGTH) {
    return t('auth.register.validationPasswordTooShort', {
      min: REGISTER_PASSWORD_MIN_LENGTH,
    })
  }

  if (form.password !== form.password_confirmation) {
    return t('auth.register.validationPasswordsMismatch')
  }

  return ''
}

function looksLikeGibberish(value) {
  const lower = value.trim().toLowerCase()

  if (lower === '') {
    return false
  }

  if (/^([a-z]{2,4})\1+$/i.test(lower)) {
    return true
  }

  const compact = lower.replace(/[^a-z]/g, '')
  const badSequences = ['asd', 'asdf', 'qwe', 'qwer', 'qweq', 'zxc', 'zcx', 'sadasd', 'zxczxc']

  if (badSequences.includes(compact)) {
    return true
  }

  if (/([a-z])\1\1/i.test(compact)) {
    return true
  }

  return false
}

function isValidInternationalPhone(phone) {
  if (phone === '' || /\p{L}/u.test(phone)) {
    return false
  }

  const digits = phone.replace(/\D+/g, '')

  return digits.length >= 8 && digits.length <= 15 && digits[0] !== '0'
}
</script>
