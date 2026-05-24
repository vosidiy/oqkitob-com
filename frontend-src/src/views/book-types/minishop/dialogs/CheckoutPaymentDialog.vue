<template>
  <dialog ref="dialogRef" class="dialog-sm mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('minishop.dialogs.paymentOverview') }}</h5>
      <button type="button" class="btn btn-icon" :disabled="isSavingSale" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>

    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
          {{ errorMessage }}
        </div>

        <div class="d-flex justify-content-between mb-3">
          <span>{{ $t('common.fields.subtotal') }}</span>
          <div class="text-right">{{ formatMoneyValue(subtotal) }}</div>
        </div>

        <div class="row justify-content-between mb-3">
          <label class="col-6 form-label" for="checkout-payment-discount">{{ $t('common.fields.discount') }}</label>
          <div class="col-6 text-right font-semibold">
            <input
              id="checkout-payment-discount"
              :value="discountInput"
              type="number"
              class="form-control text-right min-h-5 h-8"
              min="0"
              step="0.1"
              :disabled="isSavingSale"
              @input="emit('update:discountInput', $event.target.value)"
              @blur="emit('normalize-discount')"
            >
          </div>
        </div>

        <div v-if="discountAmount > 0" class="row justify-content-between mb-3">
          <span class="col-6">{{ $t('common.fields.total') }}</span>
          <div class="col-6 text-right">{{ formatMoneyValue(total) }}</div>
        </div>

        <hr>

        <nav class="tabs-boxed w-full mb-4">
          <label tabindex="0" class="tab-link flex-1">
            <input
              :checked="paymentMethod === 'cash'"
              type="radio"
              name="checkout-payment-method"
              value="cash"
              :disabled="isSavingSale"
              @change="emit('update:paymentMethod', 'cash')"
            >
            {{ $t('minishop.paymentMethods.cash') }}
          </label>
          <label tabindex="0" class="tab-link flex-1">
            <input
              :checked="paymentMethod === 'card'"
              type="radio"
              name="checkout-payment-method"
              value="card"
              :disabled="isSavingSale"
              @change="emit('update:paymentMethod', 'card')"
            >
            {{ $t('minishop.paymentMethods.card') }}
          </label>
        </nav>

        <div class="mb-1">
          <div class="text-right font-semibold">
            <input
              id="checkout-payment-paid"
              :value="paidInput"
              type="number"
              class="form-control text-xl font-semibold"
              min="0"
              step="0.01"
              :disabled="isSavingSale"
              @input="handlePaidInput"
              @blur="emit('normalize-paid')"
            >
          </div>
        </div>

        <div class="mb-2">
          <div v-if="changeAmount > 0" class="text-green">
            <span>{{ $t('minishop.sales.returnChange') }}: </span>
            <strong class="text-sm">{{ formatMoneyValue(changeAmount) }}</strong>
          </div>
          <div v-else-if="remainingAmount > 0" class="text-orange">
            <span>{{ $t('minishop.sales.remainingDebt') }}: </span>
            <strong class="text-sm">{{ formatMoneyValue(remainingAmount) }}</strong>
          </div>
          <div v-else class="text-green">
            <strong class="text-sm">{{ $t('common.states.paidInFull') }}</strong>
          </div>
        </div>

        <div class="pt-4">
          <button type="submit" class="btn btn-lg w-full btn-primary" :disabled="isSavingSale || cartItemsLength === 0">
            <span v-if="isSavingSale">{{ $t('common.states.saving') }}</span>
            <strong v-else>{{ $t('minishop.dialogs.savePayment') }}</strong>
          </button>

          <div class="text-center mt-4">
            <a role="button" href="#" class="text-secondary btn-sm" :disabled="isSavingSale" @click.prevent="close">
              {{ $t('common.actions.cancel') }}
            </a>
          </div>
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  cartItemsLength: {
    type: Number,
    default: 0,
  },
  changeAmount: {
    type: Number,
    default: 0,
  },
  discountAmount: {
    type: Number,
    default: 0,
  },
  discountInput: {
    type: String,
    default: '0.00',
  },
  errorMessage: {
    type: String,
    default: '',
  },
  isSavingSale: {
    type: Boolean,
    default: false,
  },
  paidInput: {
    type: String,
    default: '0.00',
  },
  paymentMethod: {
    type: String,
    default: 'cash',
  },
  remainingAmount: {
    type: Number,
    default: 0,
  },
  subtotal: {
    type: Number,
    default: 0,
  },
  total: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits([
  'cancel',
  'close',
  'mark-paid-manually-edited',
  'normalize-discount',
  'normalize-paid',
  'submit',
  'update:discountInput',
  'update:paidInput',
  'update:paymentMethod',
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

function handlePaidInput(event) {
  emit('mark-paid-manually-edited')
  emit('update:paidInput', event.target.value)
}

function formatMoneyValue(value) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())
  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue.toFixed(2) : '0.00'
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
