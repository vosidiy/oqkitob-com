<template>
  <CreateCustomerDialog
    ref="dialogRef"
    :error-message="errorMessage"
    :form="form"
    :is-submit-disabled="isSubmitDisabled"
    :is-submitting="isSubmitting"
    name-id="customers-tab-create-name"
    :name-placeholder="$t('minishop.customers.enterCustomerName')"
    note-id="customers-tab-create-note"
    :note-placeholder="$t('minishop.customers.optionalNote')"
    :note-rows="4"
    phone-id="customers-tab-create-phone"
    :phone-placeholder="$t('minishop.customers.optionalPhone')"
    :submit-label="$t('common.actions.create')"
    :title="$t('minishop.customers.createCustomer')"
    @cancel="emit('cancel', $event)"
    @close="emit('close')"
    @submit="emit('submit')"
  />
</template>

<script setup>
import { ref } from 'vue'
import CreateCustomerDialog from './CreateCustomerDialog.vue'

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
})

const emit = defineEmits(['cancel', 'close', 'submit'])
const dialogRef = ref(null)

function open() {
  dialogRef.value?.open()
}

function close() {
  dialogRef.value?.close()
}

function isOpen() {
  return dialogRef.value?.isOpen() === true
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
