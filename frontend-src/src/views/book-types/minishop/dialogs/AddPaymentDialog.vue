<template>
  <dialog ref="dialogRef" :class="dialogClass" @cancel="emit('cancel', $event)" @close="handleClose">
    <header class="dialog-header">
      <h5>{{ $t('minishop.sales.addPayment') }}</h5>
      <button class="btn btn-icon" :disabled="isSaving" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>
    <div class="dialog-body">
      <form @submit.prevent="handleSubmit">
        <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
          {{ errorMessage }}
        </div>

        <div class="d-flex justify-content-between mb-3">
          <span>{{ $t('common.fields.subtotal') }}</span>
          <div class="text-right font-semibold">{{ formatMoney(paymentSummarySubtotal) }}</div>
        </div>

        <div class="row justify-content-between mb-3">
          <label class="col-6 form-label" :for="discountInputId">{{ $t('common.fields.discount') }}</label>
          <div class="col-6 text-right font-semibold">
            <input
              :id="discountInputId"
              v-model.trim="discountInput"
              type="number"
              class="form-control min-h-5 h-8 font-semibold"
              min="0"
              step="0.01"
              :disabled="isSaving"
              @blur="normalizeDiscountInput"
            >
          </div>
        </div>

        <div v-if="paymentSummaryDiscountAmount > 0" class="row justify-content-between mb-3">
          <span class="col-6">{{ $t('common.fields.total') }}</span>
          <div class="col-6 text-right font-semibold">{{ formatMoney(paymentSummaryTotal) }}</div>
        </div>

        <hr>

        <div class="mb-3">
          <label class="form-label d-block">{{ $t('common.fields.method') }}</label>
          <div class="d-flex gap-4">
            <label class="form-check d-flex align-items-center gap-2">
              <input
                v-model="paymentMethod"
                class="form-check-input"
                :name="paymentMethodName"
                type="radio"
                value="cash"
                :disabled="isSaving"
              >
              <span>{{ $t('minishop.paymentMethods.cash') }}</span>
            </label>
            <label class="form-check d-flex align-items-center gap-2">
              <input
                v-model="paymentMethod"
                class="form-check-input"
                :name="paymentMethodName"
                type="radio"
                value="card"
                :disabled="isSaving"
              >
              <span>{{ $t('minishop.paymentMethods.card') }}</span>
            </label>
          </div>
        </div>

        <div class="row justify-content-between mb-3">
          <label class="col-6 form-label" :for="paidInputId">{{ $t('common.fields.paid') }}</label>
          <div class="col-6 text-right font-semibold">
            <input
              :id="paidInputId"
              v-model.trim="paidInput"
              type="number"
              class="form-control min-h-5 h-8 font-semibold"
              min="0"
              step="0.01"
              :disabled="isSaving"
              @blur="normalizePaidInput"
            >
          </div>
        </div>

        <div class="mb-5">
          <div v-if="paymentSummaryChangeAmount > 0" class="d-flex justify-content-between gap-3 text-green">
            <span>{{ $t('minishop.sales.returnChange') }}</span>
            <strong>{{ formatMoney(paymentSummaryChangeAmount) }}</strong>
          </div>
          <div v-else-if="paymentSummaryRemainingAmount > 0" class="d-flex justify-content-between gap-3 text-orange">
            <span>{{ $t('minishop.sales.remainingDebt') }}</span>
            <strong>{{ formatMoney(paymentSummaryRemainingAmount) }}</strong>
          </div>
          <div v-else class="d-flex justify-content-between gap-3 text-green">
            <span>{{ $t('common.fields.status') }}</span>
            <strong>{{ $t('common.states.paidInFull') }}</strong>
          </div>
        </div>

        <div class="border-top d-flex pt-4 gap-2">
          <button type="button" class="btn btn-default flex-1" :disabled="isSaving" @click="close">
            {{ $t('common.actions.cancel') }}
          </button>
          <button
            type="submit"
            class="btn btn-primary flex-1"
            :disabled="isSaving || !sale || paymentSummaryPaidAmount <= 0"
          >
            <span v-if="isSaving">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('minishop.sales.addPayment') }}</span>
          </button>
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  dialogClass: {
    type: String,
    default: 'dialog-sm',
  },
  discountInputId: {
    type: String,
    required: true,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  initialPaymentMethod: {
    type: String,
    default: 'cash',
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
  paidInputId: {
    type: String,
    required: true,
  },
  paymentMethodName: {
    type: String,
    required: true,
  },
  sale: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['cancel', 'close', 'submit'])
const dialogRef = ref(null)
const discountInput = ref('0.00')
const paidInput = ref('0.00')
const paymentMethod = ref(props.initialPaymentMethod)

const paymentSummarySubtotal = computed(() => {
  return parseNonNegativeAmount(props.sale?.subtotal_amount ?? 0, 0)
})

const paymentSummaryDiscountAmount = computed(() => {
  return Math.min(parseNonNegativeAmount(discountInput.value, 0), paymentSummarySubtotal.value)
})

const paymentSummaryTotal = computed(() => {
  return Math.max(paymentSummarySubtotal.value - paymentSummaryDiscountAmount.value, 0)
})

const paymentSummaryRecordedPaidAmount = computed(() => {
  return parseNonNegativeAmount(props.sale?.paid_amount ?? 0, 0)
})

const paymentSummaryRemainingBeforePayment = computed(() => {
  return Math.max(paymentSummaryTotal.value - paymentSummaryRecordedPaidAmount.value, 0)
})

const paymentSummaryPaidAmount = computed(() => {
  return parseNonNegativeAmount(paidInput.value, 0)
})

const paymentSummaryAppliedAmount = computed(() => {
  return paymentMethod.value === 'cash'
    ? Math.min(paymentSummaryPaidAmount.value, paymentSummaryRemainingBeforePayment.value)
    : paymentSummaryPaidAmount.value
})

const paymentSummaryChangeAmount = computed(() => {
  return paymentMethod.value === 'cash' && paymentSummaryPaidAmount.value > paymentSummaryRemainingBeforePayment.value
    ? paymentSummaryPaidAmount.value - paymentSummaryRemainingBeforePayment.value
    : 0
})

const paymentSummaryRemainingAmount = computed(() => {
  return Math.max(
    paymentSummaryTotal.value - paymentSummaryRecordedPaidAmount.value - paymentSummaryAppliedAmount.value,
    0,
  )
})

watch(
  [paymentMethod, paymentSummaryRemainingBeforePayment],
  ([nextMethod, nextRemaining]) => {
    if (nextMethod === 'card' && paymentSummaryPaidAmount.value > nextRemaining) {
      paidInput.value = formatMoney(nextRemaining)
    }
  },
)

function open() {
  resetForm()

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

function handleClose() {
  resetForm()
  emit('close')
}

function handleSubmit() {
  if (!props.sale || props.isSaving || paymentSummaryPaidAmount.value <= 0) {
    return
  }

  emit('submit', {
    amount: paymentSummaryPaidAmount.value,
    discount_amount: paymentSummaryDiscountAmount.value,
    payment_method: paymentMethod.value,
  })
}

function normalizeDiscountInput() {
  discountInput.value = formatMoney(paymentSummaryDiscountAmount.value)
}

function normalizePaidInput() {
  const normalizedAmount = paymentSummaryPaidAmount.value

  if (paymentMethod.value === 'card' && normalizedAmount > paymentSummaryRemainingBeforePayment.value) {
    paidInput.value = formatMoney(paymentSummaryRemainingBeforePayment.value)
    return
  }

  paidInput.value = formatMoney(normalizedAmount)
}

function resetForm() {
  discountInput.value = formatMoney(props.sale?.discount_amount ?? 0)
  paidInput.value = formatMoney(props.sale?.due_amount ?? 0)
  paymentMethod.value = props.initialPaymentMethod
}

function parseNonNegativeAmount(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())
  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue : fallback
}

function formatMoney(value) {
  return parseNonNegativeAmount(value, 0).toFixed(2)
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
