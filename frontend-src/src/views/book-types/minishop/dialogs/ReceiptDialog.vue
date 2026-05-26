<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5> ✅ {{ $t('minishop.dialogs.saleSaved') }}</h5>
      <button class="btn btn-icon" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>

    <div v-if="receiptState" class="dialog-body" id="minishop-receipt-content">
      <div class="mb-3">
        <div><strong>{{ $t('common.fields.book') }}:</strong> {{ book.title }}</div>
        <div><strong>{{ $t('common.fields.receipt') }} ID:</strong> {{ receiptState.sale.id }}</div>
        <div><strong>{{ $t('common.fields.soldAt') }}:</strong> {{ receiptState.sale.sold_at }}</div>
        <div><strong>{{ $t('common.fields.currency') }}:</strong> {{ receiptState.sale.currency_code }}</div>
        <div v-if="receiptState.sale.customer_name">
          <strong>{{ $t('common.fields.customer') }}:</strong>
          {{ receiptState.sale.customer_name }}
          <span v-if="receiptState.sale.customer_phone"> · {{ receiptState.sale.customer_phone }}</span>
        </div>
      </div>

      <div class="mb-3">
        <table class="table table-bordered mb-0">
          <thead>
            <tr>
              <th>{{ $t('common.fields.item') }}</th>
              <th>{{ $t('minishop.main.quantityShort') }}</th>
              <th class="text-right">{{ $t('common.fields.price') }}</th>
              <th class="text-right">{{ $t('common.fields.total') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in receiptState.items" :key="item.id">
              <td>{{ item.product_name }}</td>
              <td>{{ item.quantity }}</td>
              <td class="text-right">{{ item.unit_price }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></td>
              <td class="text-right">{{ item.line_total }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-col mb-4 align-items-end">
        <div class="d-flex justify-content-between">
          <span>{{ $t('common.fields.subtotal') }}</span>
          <strong class="min-w-40 text-right">{{ receiptState.sale.subtotal_amount }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div class="d-flex justify-content-between">
          <span>{{ $t('common.fields.discount') }}</span>
          <strong class="min-w-40 text-right">- {{ receiptState.sale.discount_amount }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div class="d-flex justify-content-between">
          <span>{{ $t('common.fields.total') }}</span>
          <strong class="min-w-40 text-right">{{ receiptState.sale.total_amount }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div class="d-flex justify-content-between">
          <span>{{ $t('common.fields.paid') }}</span>
          <strong class="min-w-40 text-right">{{ formatMoneyValue(receiptState.tenderedAmount) }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div v-if="receiptState.changeAmount > 0" class="d-flex justify-content-between text-green">
          <span>{{ $t('minishop.sales.returnChange') }}</span>
          <strong class="min-w-40 text-right">{{ formatMoneyValue(receiptState.changeAmount) }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div v-else-if="Number(receiptState.sale.due_amount) > 0" class="d-flex justify-content-between text-orange">
          <span>{{ $t('minishop.sales.remainingDebt') }}</span>
          <strong class="min-w-40 text-right">{{ receiptState.sale.due_amount }} <small class="currency-code">{{ receiptState.sale.currency_code }}</small></strong>
        </div>
        <div v-else class="d-flex justify-content-between text-green">
          <span>{{ $t('common.fields.status') }}</span>
          <strong class="min-w-40 text-right">{{ $t('common.states.paidInFull') }}</strong>
        </div>
      </div>

      <div class="d-flex flex-row gap-2 border-top pt-3">
        <button type="button" class="btn flex-1 btn-outline" @click="emit('print')">
          🖨️ {{ $t('common.actions.printReceipt') }}
        </button>
        <button type="button" class="btn flex-1 btn-primary" @click="close">
          {{ $t('common.actions.ok') }}
        </button>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  book: {
    type: Object,
    required: true,
  },
  receiptState: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['cancel', 'close', 'print'])
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
