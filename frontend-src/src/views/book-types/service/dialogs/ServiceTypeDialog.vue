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

        <div class="mb-4">
          <label class="form-label" for="service-type-name">{{ $t('common.fields.name') }}</label>
          <input
            id="service-type-name"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :placeholder="$t('service.dialogs.serviceNamePlaceholder')"
            :disabled="isSubmitting"
            required
          >
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="service-type-unit">{{ $t('service.fields.defaultUnit') }}</label>
            <select
              id="service-type-unit"
              v-model="form.default_unit"
              class="form-select"
              :disabled="isSubmitting"
              required
            >
              <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                {{ $t(unit.labelKey) }}
              </option>
            </select>
          </div>

          <div class="col-6 mb-4">
            <label class="form-label" for="service-type-price">{{ $t('service.fields.defaultPrice') }}</label>
            <div class="relative">
              <input
                id="service-type-price"
                v-model.trim="form.default_price"
                type="number"
                class="form-control"
                min="0"
                step="0.01"
                :placeholder="$t('service.dialogs.moneyPlaceholder')"
                :disabled="isSubmitting"
                required
              >
              <small class="currency-code text-secondary text-default bg-neutral-50 absolute right-2 top-1 p-1">
                {{ book.currency_code }}
              </small>
            </div>
          </div>
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
import { SERVICE_UNIT_OPTIONS } from './orderItemRow'

defineProps({
  book: {
    type: Object,
    required: true,
  },
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
const unitOptions = SERVICE_UNIT_OPTIONS

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
