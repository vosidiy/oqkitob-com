<template>
  <dialog ref="dialogRef" class="dialog-md" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <div class="d-flex align-items-center gap-2">
        <b class="avatar bg-primary-300 text-white avatar-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </b>
        <h5>{{ $t('profileDialog.title') }}</h5>
      </div>
      <button type="button" class="btn btn-icon" :disabled="isProfileActionPending" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24">
          <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
        </svg>
      </button>
    </header>

    <div class="dialog-body">
      <article class="d-flex gap-2 py-4 pt-0 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12">{{ $t('profileDialog.account') }}</label>
        <div class="col-8 mobile:col-12">
          <p class="text-secondary mb-1">
            {{ $t('profileDialog.accountCreatedAt') }}: {{ formattedProfileCreatedAt }}
          </p>
          <b>{{ user?.email || '-' }}</b>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12" for="profile-name">{{ $t('profileDialog.name') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex gap-2 justify-content-between mobile:flex-col">
            <input
              id="profile-name"
              v-model.trim="profileForm.name"
              type="text"
              class="form-control"
              :placeholder="$t('profileDialog.namePlaceholder')"
              :disabled="isProfileActionPending"
            >
            <button
              type="button"
              class="btn btn-primary flex-shrink-0"
              :disabled="isNameSaveDisabled"
              @click="emit('save-name')"
            >
              <span v-if="isSavingProfileName">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.save') }}</span>
            </button>
          </div>

          <div v-if="profileErrorMessages.name" class="alert alert-danger mt-2 mb-0" role="alert">
            {{ profileErrorMessages.name }}
          </div>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12" for="profile-phone">{{ $t('profileDialog.phone') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex gap-2 justify-content-between mobile:flex-col">
            <input
              id="profile-phone"
              v-model.trim="profileForm.phone"
              type="text"
              class="form-control"
              :placeholder="$t('profileDialog.phonePlaceholder')"
              :disabled="isProfileActionPending"
            >
            <button
              type="button"
              class="btn btn-primary flex-shrink-0"
              :disabled="isPhoneSaveDisabled"
              @click="emit('save-phone')"
            >
              <span v-if="isSavingProfilePhone">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.save') }}</span>
            </button>
          </div>

          <div v-if="profileErrorMessages.phone" class="alert alert-danger mt-2 mb-0" role="alert">
            {{ profileErrorMessages.phone }}
          </div>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12">{{ $t('profileDialog.password') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex justify-content-between align-items-center gap-2 mobile:flex-col mobile:align-items-stretch">
            <span>{{ $t('profileDialog.passwordMask') }}</span>
            <button
              v-if="!showPasswordUpdateForm"
              type="button"
              class="btn btn-default"
              :disabled="isProfileActionPending"
              @click="emit('open-password-form')"
            >
              {{ $t('profileDialog.changePassword') }}
            </button>
          </div>

          <form v-if="showPasswordUpdateForm" class="mt-3" @submit.prevent="emit('save-password')">
            <div class="mb-4">
              <label class="form-label" for="profile-current-password">{{ $t('profileDialog.currentPassword') }}</label>
              <input
                id="profile-current-password"
                v-model="passwordForm.current_password"
                type="password"
                class="form-control"
                :placeholder="$t('profileDialog.currentPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="current-password"
              >
            </div>

            <div class="mb-2">
              <label class="form-label" for="profile-new-password">{{ $t('profileDialog.newPassword') }}</label>
              <input
                id="profile-new-password"
                v-model="passwordForm.new_password"
                type="password"
                class="form-control mb-2"
                :placeholder="$t('profileDialog.newPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="new-password"
              >
              <input
                id="profile-new-password-confirmation"
                v-model="passwordForm.new_password_confirmation"
                type="password"
                class="form-control mb-2"
                :placeholder="$t('profileDialog.repeatNewPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="new-password"
              >
            </div>

            <div v-if="profileErrorMessages.password" class="alert alert-danger mb-3" role="alert">
              {{ profileErrorMessages.password }}
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isPasswordSaveDisabled">
                <span v-if="isSavingProfilePassword">{{ $t('common.states.saving') }}</span>
                <span v-else>{{ $t('profileDialog.saveNewPassword') }}</span>
              </button>
              <button
                type="button"
                class="btn btn-default"
                :disabled="isProfileActionPending"
                @click="emit('close-password-form')"
              >
                {{ $t('common.actions.cancel') }}
              </button>
            </div>
          </form>
        </div>
      </article>

      <div class="d-flex gap-2 mt-4 justify-content-between mobile:flex-col">
        <button
          type="button"
          class="btn btn-plain border text-red"
          :disabled="isLoggingOut || isProfileActionPending"
          @click="emit('logout')"
        >
          <span v-if="isLoggingOut">{{ $t('common.states.loggingOut') }}</span>
          <span v-else>{{ $t('common.actions.logout') }}</span>
        </button>
        <button type="button" class="btn btn-default" :disabled="isProfileActionPending" @click="close">
          {{ $t('common.actions.close') }}
        </button>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  formattedProfileCreatedAt: {
    type: String,
    default: '-',
  },
  isLoggingOut: {
    type: Boolean,
    default: false,
  },
  isNameSaveDisabled: {
    type: Boolean,
    default: false,
  },
  isPasswordSaveDisabled: {
    type: Boolean,
    default: false,
  },
  isPhoneSaveDisabled: {
    type: Boolean,
    default: false,
  },
  isProfileActionPending: {
    type: Boolean,
    default: false,
  },
  isSavingProfileName: {
    type: Boolean,
    default: false,
  },
  isSavingProfilePassword: {
    type: Boolean,
    default: false,
  },
  isSavingProfilePhone: {
    type: Boolean,
    default: false,
  },
  passwordForm: {
    type: Object,
    required: true,
  },
  profileErrorMessages: {
    type: Object,
    required: true,
  },
  profileForm: {
    type: Object,
    required: true,
  },
  showPasswordUpdateForm: {
    type: Boolean,
    default: false,
  },
  user: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits([
  'cancel',
  'close',
  'close-password-form',
  'logout',
  'open-password-form',
  'save-name',
  'save-password',
  'save-phone',
])
const dialogRef = ref(null)

function open() {
  if (!dialogRef.value?.open) {
    dialogRef.value?.showModal()
  }
}

function close() {
  if (dialogRef.value?.open) {
    dialogRef.value.close()
  }
}

function isOpen() {
  return dialogRef.value?.open === true
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
