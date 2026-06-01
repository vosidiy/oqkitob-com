<template>
  <dialog ref="dialogRef" class="dialog-md mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <div class="mr-auto">
        <h5>🧾 {{ $t('minishop.sales.receipt') }} </h5>
        <p v-if="sale" class="text-secondary text-sm">{{ sale.id }}</p>
      </div>      
      <button class="btn btn-icon" :disabled="isLoadingReceiptDetail" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>
    <div class="dialog-body">
      <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
        {{ errorMessage }}
      </div>

      <div v-if="isLoadingReceiptDetail" class="text-secondary">
        {{ $t('minishop.sales.loadingReceipt') }}
      </div>

      <div v-else-if="sale">

        <article class="mb-3">
          <p><strong>{{ $t('common.fields.soldAt') }}:</strong> {{ formatDateTime(sale.sold_at) }}</p>
          <p><strong>{{ $t('common.fields.customer') }}:</strong>
            {{ sale.customer_name || $t('minishop.sales.noCustomer') }}
            <span v-if="sale.customer_phone"> · {{ sale.customer_phone }}</span>
          </p>
          <p><strong>{{ $t('common.fields.currency') }}:</strong> {{ sale.currency_code }}</p>
          <p><strong>{{ $t('common.fields.status') }}:</strong> {{ $t('minishop.paymentLabels.' + sale.payment_status) }}</p>
          <p v-if="sale.note"> <strong>{{ $t('common.fields.note') }}</strong>  {{ sale.note }} </p>
        </article>

        <div class="table-responsive mb-4">
          <table class="table table-sm mb-0">
            <thead>
              <tr>
                <th>{{ $t('common.fields.item') }}</th>
                <th>{{ $t('minishop.main.quantityShort') }}</th>
                <th class="text-right">{{ $t('common.fields.price') }}</th>
                <th class="text-right"> = </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.product_name }}</td>
                <td>{{ formatQuantity(item.quantity) }}</td>
                <td class="text-right">{{ formatMoney(item.unit_price) }} <small class="currency-code">{{ sale.currency_code }}</small></td>
                <td class="text-right">{{ formatMoney(item.line_total) }} <small class="currency-code">{{ sale.currency_code }}</small></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex flex-col align-items-end pr-2">
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('common.fields.subtotal') }}</span>
            <strong>{{ formatMoney(sale.subtotal_amount) }} <small class="currency-code">{{ sale.currency_code }}</small></strong>
          </div>
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('common.fields.discount') }}</span>
            <strong>- {{ formatMoney(sale.discount_amount) }} <small class="currency-code">{{ sale.currency_code }}</small></strong>
          </div>
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('minishop.main.totalForPay') }}</span>
            <strong>{{ formatMoney(sale.total_amount) }} <small class="currency-code">{{ sale.currency_code }}</small></strong>
          </div>
          <div class="d-flex col-6 justify-content-between gap-3">
            <span class="text-green">{{ $t('common.fields.paid') }}</span>
            <strong class="text-green">{{ formatMoney(sale.paid_amount) }} <small class="currency-code">{{ sale.currency_code }}</small></strong>
          </div>
          <div v-if="Number(sale.due_amount) > 0" class="d-flex col-6 justify-content-between gap-3 text-orange">
            <span>{{ $t('minishop.sales.remainingDebt') }}</span>
            <strong>{{ formatMoney(sale.due_amount) }} <small class="currency-code">{{ sale.currency_code }}</small></strong>
          </div>
          <div v-else class="d-flex col-6 justify-content-between gap-3 text-green">
            <span>{{ $t('common.fields.status') }}</span>
            <strong>{{ $t('common.states.paidInFull') }}</strong>
          </div>
        </div>

        <article class="mt-4 py-4 border-top">
          
          <h6 class="text-lg mb-3"> 💵  {{ $t('minishop.sales.paymentRecords') }}</h6>

          <div v-if="payments.length === 0" class="text-secondary mb-4">
            <p>{{ $t('minishop.sales.noPaymentRecords') }}</p>
          </div>

          <ul v-else class="mb-4">
            <li v-for="payment in payments" :key="payment.id" class="border mb-1 border-color-green-300 rounded p-2 bg-green-100">
              <div class="d-flex justify-content-between gap-2">
                <div>
                  <p><strong>{{ $t('common.fields.date') }}:</strong> {{ formatDateTime(payment.paid_at || payment.created_at) }}</p>
                  <a href="#"
                    role="button"
                    class="link text-secondary"
                    :disabled="deletingPaymentId === payment.id || isSavingPayment || isDeletingReceipt"
                    @click="emit('delete-payment', payment)"
                  >
                    <span v-if="deletingPaymentId === payment.id">{{ $t('common.states.deleting') }}</span>
                    <span v-else> 🗑️ {{ $t('minishop.sales.deletePayment') }}</span>
                  </a>
                </div>
                <div class="text-right">
                  <p><strong class="text-green"> {{ formatMoney(payment.amount) }} <small class="currency-code">{{ payment.currency_code }}</small></strong></p>
                  <p> {{ $t('common.fields.method') }}: {{ $t('minishop.paymentMethods.' + payment.payment_method) }}</p>
                </div>
              </div>
            </li>
          </ul>

          <button
            v-if="canAddPaymentToReceipt"
            type="button"
            class="btn btn-green mr-2"
            :disabled="isLoadingReceiptDetail || isSavingPayment || isDeletingReceipt"
            @click="emit('open-add-payment')"
          >
            {{ $t('minishop.sales.addPayment') }}
          </button>
        </article>
      </div>

      <footer class="pt-3 border-top">
        <button
            type="button"
            class="btn text-red"
            :disabled="!sale || isLoadingReceiptDetail || isDeletingReceipt || isSavingPayment"
            @click="emit('delete-receipt')"
          >
            <span v-if="isDeletingReceipt">{{ $t('common.states.deleting') }}</span>
            <span v-else>{{ $t('minishop.sales.deleteSale') }}</span>
        </button>
        <div class="float-right">
          <button type="button" class="btn ml-2 btn-default" :disabled="isLoadingReceiptDetail || isDeletingReceipt" @click="close">
            {{ $t('common.actions.ok') }}
          </button>
        </div>
      </footer>
      
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay } from '@/utils/quantity'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
  canAddPaymentToReceipt: {
    type: Boolean,
    default: false,
  },
  deletingPaymentId: {
    type: String,
    default: '',
  },
  errorMessage: {
    type: String,
    default: '',
  },
  isDeletingReceipt: {
    type: Boolean,
    default: false,
  },
  isLoadingReceiptDetail: {
    type: Boolean,
    default: false,
  },
  isSavingPayment: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array,
    default: () => [],
  },
  payments: {
    type: Array,
    default: () => [],
  },
  sale: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['cancel', 'close', 'delete-payment', 'delete-receipt', 'open-add-payment'])
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

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

function formatQuantity(value) {
  return formatQuantityDisplay(value)
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
