<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ title }}</h5>
      <button class="btn btn-icon" :disabled="isSubmitting" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>

    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
          {{ errorMessage }}
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="service-client-name">{{ $t('common.fields.name') }}</label>
            <input
              id="service-client-name"
              v-model.trim="form.name"
              type="text"
              class="form-control"
              :placeholder="$t('service.dialogs.customerNamePlaceholder')"
              :disabled="isSubmitting"
              required
            >
          </div>

          <div class="col-6 mb-4">
            <label class="form-label" for="service-client-phone">{{ $t('common.fields.phone') }}</label>
            <input
              id="service-client-phone"
              v-model.trim="form.phone"
              type="text"
              class="form-control"
              :placeholder="$t('service.dialogs.phonePlaceholder')"
              :disabled="isSubmitting"
              required
            >
          </div>
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="service-client-messenger">{{ $t('service.fields.messenger') }}</label>
            <input
              id="service-client-messenger"
              v-model.trim="form.messenger"
              type="text"
              class="form-control"
              :placeholder="$t('service.dialogs.messengerPlaceholder')"
              :disabled="isSubmitting"
            >
          </div>

          <div class="col-6 mb-4">
            <label class="form-label" for="service-client-address">{{ $t('service.fields.address') }}</label>
            <input
              id="service-client-address"
              v-model.trim="form.address"
              type="text"
              class="form-control"
              :placeholder="$t('service.dialogs.addressPlaceholder')"
              :disabled="isSubmitting"
            >
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="service-client-location">{{ $t('service.fields.location') }}</label>
          <input
            id="service-client-location"
            v-model.trim="form.location"
            type="text"
            class="form-control"
            :placeholder="$t('service.dialogs.locationPlaceholder')"
            :disabled="isSubmitting"
          >
        </div>

        <div class="pt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitDisabled">
            <span v-if="isSubmitting">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ submitLabel }}</span>
          </button>
          <button type="button" class="btn btn-default" :disabled="isSubmitting" @click="close">
            {{ $t('common.actions.cancel') }}
          </button>
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  errorMessage: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  isSubmitDisabled: {
    type: Boolean,
    default: false,
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['cancel', 'close', 'submit'])
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
