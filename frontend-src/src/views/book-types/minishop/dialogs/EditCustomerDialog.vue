<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('minishop.customers.editCustomerTitle') }}</h5>
      <button class="btn btn-icon" :disabled="isUpdatingCustomer" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>
    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <div class="mb-4">
          <label class="form-label" for="customers-tab-edit-name">{{ $t('common.fields.name') }}</label>
          <input
            id="customers-tab-edit-name"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :placeholder="$t('minishop.customers.enterCustomerName')"
            :disabled="isUpdatingCustomer"
            required
          >
        </div>

        <div class="mb-4">
          <label class="form-label" for="customers-tab-edit-phone">{{ $t('common.fields.phone') }}</label>
          <input
            id="customers-tab-edit-phone"
            v-model.trim="form.phone"
            type="text"
            class="form-control"
            :placeholder="$t('minishop.customers.optionalPhone')"
            :disabled="isUpdatingCustomer"
          >
        </div>

        <div class="mb-4">
          <label class="form-label" for="customers-tab-edit-note">{{ $t('common.fields.note') }}</label>
          <textarea
            id="customers-tab-edit-note"
            v-model.trim="form.note"
            class="form-control"
            rows="4"
            :placeholder="$t('minishop.customers.optionalNote')"
            :disabled="isUpdatingCustomer"
          ></textarea>
        </div>

        <div class="pt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitDisabled">
            <span v-if="isUpdatingCustomer">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('common.actions.saveChanges') }}</span>
          </button>
          <button type="button" class="btn btn-default" :disabled="isUpdatingCustomer" @click="close">
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
  isUpdatingCustomer: {
    type: Boolean,
    default: false,
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
