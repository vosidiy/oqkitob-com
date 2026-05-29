<template>
  <dialog ref="dialogRef" class="dialog-sm mt-10" @cancel="emit('cancel', $event)" @close="handleClose">
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
          <div class="text-right">{{ formatMoney(paymentSummarySubtotal) }} <small class="currency-code">{{ props.sale?.currency_code }}</small></div>
        </div>

        <div class="row justify-content-between mb-3">
          <label class="col-6 form-label" :for="discountInputId">{{ $t('common.fields.discount') }}</label>
          <div class="col-6 text-right font-semibold">
            <div class="relative">
              <input
                :id="discountInputId"
                v-model.trim="discountInput"
                type="number"
                class="form-control min-h-5 h-8"
                min="0"
                step="0.1"
                :disabled="isSaving"
                @blur="normalizeDiscountInput"
              >
              <small class="text-secondary p-1 absolute right-1 bg-neutral-50 top-1"> {{ props.sale?.currency_code }}  </small>
            </div>
          </div>
        </div>

        <div v-if="paymentSummaryDiscountAmount > 0" class="row justify-content-between mb-3">
          <span class="col-6">{{ $t('common.fields.total') }}</span>
          <div class="col-6 text-right">{{ formatMoney(paymentSummaryTotal) }} <small class="currency-code">{{ props.sale?.currency_code }}</small></div>
        </div>

        <hr>

        <nav class="tabs-boxed w-full mb-4">
            <label tabindex="0" class="tab-link flex-1">
              <input
                v-model="paymentMethod"
                :name="paymentMethodName"
                type="radio"
                value="cash"
                :disabled="isSaving"
              >
              {{ $t('minishop.paymentMethods.cash') }}  
            </label>
            <label tabindex="0" class="tab-link flex-1">
              <input
                v-model="paymentMethod"
                :name="paymentMethodName"
                type="radio"
                value="card"
                :disabled="isSaving"
              >
              {{ $t('minishop.paymentMethods.card') }} 
            </label>
        </nav>

        <div class="mb-1 relative">
            <input
              :id="paidInputId"
              v-model.trim="paidInput"
              type="number"
              class="form-control text-xl font-semibold"
              min="0"
              step="0.01"
              :disabled="isSaving"
              @blur="normalizePaidInput"
            >
            <small class="text-secondary p-1 text-lg absolute right-1 bg-neutral-50 top-1"> {{ props.sale?.currency_code }}  </small>
        </div>

        <div class="mb-2">
          <div v-if="paymentSummaryChangeAmount > 0" class="text-green">
            <span>{{ $t('minishop.sales.returnChange') }}: </span> 
            <strong class="text-sm">{{ formatMoney(paymentSummaryChangeAmount) }}<small class="currency-code"> {{ props.sale?.currency_code }}</small></strong>
          </div>
          <div v-else-if="paymentSummaryRemainingAmount > 0" class="text-orange">
            <span>{{ $t('minishop.sales.remainingDebt') }}: </span> 
            <strong class="text-sm">{{ formatMoney(paymentSummaryRemainingAmount) }} <small class="currency-code"> {{ props.sale?.currency_code }}</small></strong>
          </div>
          <div v-else class="text-green">
            <strong class="text-sm">{{ $t('common.states.paidInFull') }}</strong>
          </div>
        </div>

        <div class="pt-2">
          <button type="submit" class="btn btn-lg w-full btn-primary"
            :disabled="isSaving || !sale || paymentSummaryPaidAmount <= 0"
          >
            <span v-if="isSaving">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('minishop.sales.addPayment') }}</span>
          </button>

          <div class="text-center mt-4">
            <a role="button" href="#" class="text-secondary btn-sm"  :disabled="isSaving" @click="close">
              {{ $t('common.actions.cancel') }}
            </a>
          </div>
        </div>
      </form>

    </div>
  </dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { formatMoneyByBookSettings } from '@/utils/money-display'

const props = defineProps({
  book: {
    type: Object,
    required: true,
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
const discountInput = ref('0')
const paidInput = ref('0')
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
      paidInput.value = normalizeMoneyInputValue(nextRemaining)
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
  discountInput.value = clampMoneyInputValue(discountInput.value, {
    max: paymentSummarySubtotal.value,
  })
}

function normalizePaidInput() {
  paidInput.value = clampMoneyInputValue(paidInput.value, {
    max: paymentMethod.value === 'card' ? paymentSummaryRemainingBeforePayment.value : null,
  })
}

function resetForm() {
  discountInput.value = normalizeMoneyInputValue(props.sale?.discount_amount ?? 0)
  paidInput.value = normalizeMoneyInputValue(props.sale?.due_amount ?? 0)
  paymentMethod.value = props.initialPaymentMethod
}

function parseNonNegativeAmount(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())
  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue : fallback
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

function normalizeMoneyInputValue(value) {
  return String(parseNonNegativeAmount(value, 0))
}

function clampMoneyInputValue(value, options = {}) {
  const { max = null } = options
  const trimmedValue = String(value ?? '').trim()

  if (trimmedValue === '') {
    return '0'
  }

  const parsedValue = Number.parseFloat(trimmedValue)

  if (!Number.isFinite(parsedValue) || parsedValue < 0) {
    return '0'
  }

  if (max != null && parsedValue > max) {
    return normalizeMoneyInputValue(max)
  }

  return trimmedValue
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
