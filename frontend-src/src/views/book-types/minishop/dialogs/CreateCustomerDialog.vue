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
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <div class="mb-4">
          <label class="form-label" :for="nameId">{{ $t('common.fields.name') }}</label>
          <input
            :id="nameId"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :placeholder="namePlaceholder"
            :disabled="isSubmitting"
            required
          >
        </div>

        <div class="mb-4">
          <label class="form-label" :for="phoneId">{{ $t('common.fields.phone') }}</label>
          <input
            :id="phoneId"
            v-model.trim="form.phone"
            type="text"
            class="form-control"
            :placeholder="phonePlaceholder"
            :disabled="isSubmitting"
          >
        </div>

        <div class="mb-4">
          <label class="form-label" :for="noteId">{{ $t('common.fields.note') }}</label>
          <textarea
            :id="noteId"
            v-model.trim="form.note"
            class="form-control"
            :rows="noteRows"
            :placeholder="notePlaceholder"
            :disabled="isSubmitting"
          ></textarea>
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

const props = defineProps({
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
  nameId: {
    type: String,
    required: true,
  },
  namePlaceholder: {
    type: String,
    default: '',
  },
  noteId: {
    type: String,
    required: true,
  },
  notePlaceholder: {
    type: String,
    default: '',
  },
  noteRows: {
    type: [Number, String],
    default: 2,
  },
  phoneId: {
    type: String,
    required: true,
  },
  phonePlaceholder: {
    type: String,
    default: '',
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
