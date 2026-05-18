<template>
  <section class="d-flex flex-1 overflow-hidden mobile:flex-col">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right  mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <select
            name="filter_time"
            class="form-select"
            :value="selectedFilterTime"
            @change="selectedFilterTime = $event.target.value"
          >
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_10_days">Last 10 days</option>
            <option value="last_20_days">Last 20 days</option>
            <option value="last_30_days">Last 30 days</option>
            <option value="previous_month">Previous month</option>
            <option value="this_year">This year</option>
            <option value="all_time">All time</option>
          </select>
        </div>
        <div  class="flex-grow">
          <input type="search" placeholder="Search" class="form-control">
        </div>
      </header>
      <div class="flex-1 overflow-y-auto">
        <div v-if="salesListErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ salesListErrorMessage }}
        </div>

        <div v-else-if="isLoadingSalesList" class="px-4 py-4 text-secondary">
          Loading sales...
        </div>

        <div v-else-if="sales.length === 0" class="px-4 py-4 text-secondary">
          No sales yet.
        </div>

        <ul v-else class="mt-1">
          <li class="p-3">
            <RouterLink :to="{ name: 'book-detail', params: { bookId: props.book.id } }" class="btn text-left w-full btn-default">
            Create sale
            </RouterLink> 
          </li>
          <li v-for="sale in sales" :key="sale.id" class="border-bottom hover:bg-neutral-100">
            <div
              role="button"
              class="px-4 p-2"
              :class="{ 'bg-primary-200': selectedSaleId === sale.id }"
              @click="selectSale(sale)"
            >
              <div class="d-flex justify-content-between gap-2">
                <div>
                  <h6> {{ formatDateTime(sale.sold_at) }} </h6>
                  <small class="text-sm text-secondary">{{ sale.id }}</small>
                  <p v-if="sale.note" class="text-sm text-secondary mt-1 mb-0">
                    {{ sale.note }}
                  </p>
                </div>
                <div class="ml-auto text-right">
                  <p>{{ formatMoney(sale.total_amount) }}</p>
                  <p v-if="Number(sale.due_amount) > 0" class="text-red mt-1 mb-0">
                    Due: {{ formatMoney(sale.due_amount) }}
                  </p>
                  <span class="text-capitalize text-sm">{{ sale.payment_status }}</span>
                </div>
              </div>
              
            </div>
          </li>
        </ul>
      </div>
    </aside>
    <aside class="col-6">
    <section class="flex-1 overflow-y-auto p-4 bg-lower h-full">
      <div v-if="selectedSaleErrorMessage" class="alert alert-danger mb-4" role="alert">
        {{ selectedSaleErrorMessage }}
      </div>

      <div v-if="isLoadingSelectedSale" class="card">
        <div class="p-10 text-secondary">Loading receipt...</div>
      </div>

      <div v-else-if="selectedSale" class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start gap-3 mb-4 mobile:flex-col">
            <div>
              <h3 class="text-2xl mb-1">Receipt</h3>
              <p class="text-secondary mb-0">{{ selectedSale.id }}</p>
            </div>

            <div class="text-right mobile:text-left">
              <p class="mb-1"><strong>Sold at:</strong> {{ formatDateTime(selectedSale.sold_at) }}</p>
              <p class="mb-1"><strong>Currency:</strong> {{ selectedSale.currency_code }}</p>
              <p class="mb-3 text-capitalize"><strong>Status:</strong> {{ selectedSale.payment_status }}</p>
              <button
                type="button"
                class="btn btn-outline text-red"
                :disabled="isDeletingSelectedSale"
                @click="handleDeleteSelectedSale"
              >
                <span v-if="isDeletingSelectedSale">Deleting...</span>
                <span v-else>Delete sale</span>
              </button>
            </div>
          </div>

          <div class="table-responsive mb-4">
            <table class="table table-sm mb-0">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedSaleItems" :key="item.id">
                  <td>{{ item.product_name }}</td>
                  <td>{{ formatQuantity(item.quantity) }}</td>
                  <td>{{ formatMoney(item.unit_price) }}</td>
                  <td>{{ formatMoney(item.line_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-col gap-2">
            <div
              v-if="selectedSale.note"
              class="d-flex justify-content-between gap-3"
            >
              <span>Note</span>
              <strong class="text-right">{{ selectedSale.note }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Subtotal</span>
              <strong>{{ formatMoney(selectedSale.subtotal_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Discount</span>
              <strong>- {{ formatMoney(selectedSale.discount_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Total</span>
              <strong>{{ formatMoney(selectedSale.total_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Paid</span>
              <strong>{{ formatMoney(selectedSale.paid_amount) }}</strong>
            </div>
            <div
              v-if="Number(selectedSale.due_amount) > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>Remaining debt</span>
              <strong>{{ formatMoney(selectedSale.due_amount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>Status</span>
              <strong>Paid in full</strong>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-primary"
              :disabled="isDeletingSelectedSale"
              @click="openPaymentDialog"
            >
              Change payment
            </button>
          </div>
        </div>
      </div>

      <div v-else class="card">
        <div class="card-body text-center py-6">
          <h3 class="h6 mb-2">Select a sale</h3>
          <p class="text-secondary mb-0">
            Choose any sale from the list to view its receipt details here.
          </p>
        </div>
      </div>
    </section>
    </aside>
  </section>


  <dialog
      ref="paymentDialog"
      class="dialog-sm"
      @cancel="handlePaymentDialogCancel"
      @close="handlePaymentDialogClose"
    >
      <header class="dialog-header">
        <h5>Change payment info</h5>
        <button class="btn btn-icon" 
             :disabled="isUpdatingPayment"
              @click="closePaymentDialog">
            <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
      <form  @submit.prevent="handleUpdatePaymentSummary">
        
          <div v-if="paymentUpdateErrorMessage" class="alert alert-danger mb-3" role="alert">
            {{ paymentUpdateErrorMessage }}
          </div>
          
          <div class="d-flex justify-content-between mb-3">
            <span class="col-6">Subtotal</span>
            <div class="col-6 text-right font-semibold">
              {{ formatMoney(paymentSummarySubtotal) }}
            </div>
          </div>

          <div class="row justify-content-between mb-3">
            <label class="col-6 form-label" for="payment-summary-discount">Discount</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="payment-summary-discount"
                v-model.trim="paymentForm.discountInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isUpdatingPayment"
                @blur="normalizePaymentDiscountInput"
              >
            </div>
          </div>

          <div class="row justify-content-between mb-2">
            <span class="col-6">Total</span>
            <div class="col-5 text-right font-semibold">
              {{ formatMoney(paymentSummaryTotal) }}
            </div>
          </div>
          
          <hr>

          <div class="row justify-content-between">
            <label class="col-6 form-label" for="payment-summary-paid">Paid</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="payment-summary-paid"
                v-model.trim="paymentForm.paidInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isUpdatingPayment"
                @blur="normalizePaymentPaidInput"
              >
            </div>
          </div>
          <hr>
          
          <div class="mb-5">
            <div
              v-if="paymentSummaryChangeAmount > 0"
              class="d-flex justify-content-between gap-3 text-green"
            >
              <span>Return change</span>
              <strong>{{ formatMoney(paymentSummaryChangeAmount) }}</strong>
            </div>
            <div
              v-else-if="paymentSummaryRemainingAmount > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>Remaining debt</span>
              <strong>{{ formatMoney(paymentSummaryRemainingAmount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>Status</span>
              <strong>Paid in full</strong>
            </div>
          </div>

          <div class="border-top d-flex pt-4 gap-2">
            <button
              type="button"
              class="btn btn-default flex-1"
              :disabled="isUpdatingPayment"
              @click="closePaymentDialog"
            >
              Cancel
            </button>
            <button type="submit" class="btn btn-primary flex-1" :disabled="isUpdatingPayment || !selectedSale">
              <span v-if="isUpdatingPayment">Saving...</span>
              <span v-else>Save</span>
            </button>
          </div>
      </form>
      </div>
    </dialog>

</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { getApiErrorMessage, isNotFoundError, isUnauthorizedError } from '@/api/errors'
import {
  deleteMinishopSale,
  fetchMinishopSale,
  fetchMinishopSales,
  updateMinishopSalePaymentSummary,
} from '@/api/minishop'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const paymentDialog = ref(null)
const sales = ref([])
const selectedSaleId = ref('')
const selectedSale = ref(null)
const selectedSaleItems = ref([])
const isLoadingSalesList = ref(false)
const isLoadingSelectedSale = ref(false)
const isDeletingSelectedSale = ref(false)
const isUpdatingPayment = ref(false)
const salesListErrorMessage = ref('')
const selectedSaleErrorMessage = ref('')
const paymentUpdateErrorMessage = ref('')
const selectedFilterTime = ref('today')

const paymentForm = reactive({
  discountInput: '0.00',
  paidInput: '0.00',
})

const paymentSummarySubtotal = computed(() => {
  return parseNonNegativeAmount(selectedSale.value?.subtotal_amount ?? 0, 0)
})

const paymentSummaryDiscountAmount = computed(() => {
  return Math.min(parseNonNegativeAmount(paymentForm.discountInput, 0), paymentSummarySubtotal.value)
})

const paymentSummaryTotal = computed(() => {
  return Math.max(paymentSummarySubtotal.value - paymentSummaryDiscountAmount.value, 0)
})

const paymentSummaryPaidAmount = computed(() => {
  return parseNonNegativeAmount(paymentForm.paidInput, 0)
})

const paymentSummaryChangeAmount = computed(() => {
  return paymentSummaryPaidAmount.value > paymentSummaryTotal.value
    ? paymentSummaryPaidAmount.value - paymentSummaryTotal.value
    : 0
})

const paymentSummaryRemainingAmount = computed(() => {
  return paymentSummaryPaidAmount.value < paymentSummaryTotal.value
    ? paymentSummaryTotal.value - paymentSummaryPaidAmount.value
    : 0
})

watch([() => props.book.id, selectedFilterTime], async () => {
  await loadSales()
}, { immediate: true })

async function loadSales() {
  isLoadingSalesList.value = true
  salesListErrorMessage.value = ''
  selectedSaleId.value = ''
  selectedSale.value = null
  selectedSaleItems.value = []
  selectedSaleErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopSales(props.book.id, {
      filter_time: selectedFilterTime.value,
      local_now: makeLocalDateTimeString(),
    })
    sales.value = Array.isArray(data.sales) ? data.sales : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    sales.value = []
    salesListErrorMessage.value = getApiErrorMessage(error, 'Unable to load sales right now.')
  } finally {
    isLoadingSalesList.value = false
  }
}

async function selectSale(sale) {
  selectedSaleId.value = sale.id
  selectedSaleErrorMessage.value = ''
  isLoadingSelectedSale.value = true

  try {
    const { data } = await fetchMinishopSale(props.book.id, sale.id)
    selectedSale.value = data.sale ?? null
    selectedSaleItems.value = Array.isArray(data.items) ? data.items : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    clearSelectedSale()
    selectedSaleErrorMessage.value = isNotFoundError(error)
      ? 'This sale is no longer available.'
      : getApiErrorMessage(error, 'Unable to load this receipt right now.')

    if (isNotFoundError(error)) {
      removeSaleFromList(sale.id)
    }
  } finally {
    isLoadingSelectedSale.value = false
  }
}

async function handleDeleteSelectedSale() {
  if (!selectedSale.value || isDeletingSelectedSale.value) {
    return
  }

  const saleId = selectedSale.value.id

  if (!window.confirm('Delete this sale and restore stock?')) {
    return
  }

  selectedSaleErrorMessage.value = ''
  isDeletingSelectedSale.value = true

  try {
    const { data } = await deleteMinishopSale(props.book.id, saleId)
    removeSaleFromList(data.saleId ?? saleId)
    clearSelectedSale()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      removeSaleFromList(saleId)
      clearSelectedSale()
      selectedSaleErrorMessage.value = 'This sale is no longer available.'
      return
    }

    selectedSaleErrorMessage.value = getApiErrorMessage(error, 'Unable to delete this sale right now.')
  } finally {
    isDeletingSelectedSale.value = false
  }
}

function openPaymentDialog() {
  if (!selectedSale.value) {
    return
  }

  paymentUpdateErrorMessage.value = ''
  paymentForm.discountInput = formatMoney(selectedSale.value.discount_amount)
  paymentForm.paidInput = formatMoney(selectedSale.value.paid_amount)

  if (!paymentDialog.value?.open) {
    paymentDialog.value?.showModal()
  }
}

function closePaymentDialog() {
  if (paymentDialog.value?.open) {
    paymentDialog.value.close()
  }
}

function handlePaymentDialogCancel(event) {
  if (isUpdatingPayment.value) {
    event.preventDefault()
  }
}

function handlePaymentDialogClose() {
  paymentUpdateErrorMessage.value = ''
  paymentForm.discountInput = '0.00'
  paymentForm.paidInput = '0.00'
  isUpdatingPayment.value = false
}

function normalizePaymentDiscountInput() {
  paymentForm.discountInput = formatMoney(paymentSummaryDiscountAmount.value)
}

function normalizePaymentPaidInput() {
  paymentForm.paidInput = formatMoney(paymentSummaryPaidAmount.value)
}

async function handleUpdatePaymentSummary() {
  if (!selectedSale.value || isUpdatingPayment.value) {
    return
  }

  paymentUpdateErrorMessage.value = ''
  isUpdatingPayment.value = true

  const saleId = selectedSale.value.id

  try {
    const { data } = await updateMinishopSalePaymentSummary(props.book.id, saleId, {
      discount_amount: paymentSummaryDiscountAmount.value,
      paid_amount: paymentSummaryPaidAmount.value,
    })

    if (!data.sale) {
      throw new Error('Sale response did not include updated sale data.')
    }

    selectedSale.value = data.sale
    patchSaleInList(data.sale)
    closePaymentDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closePaymentDialog()
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      removeSaleFromList(saleId)
      clearSelectedSale()
      closePaymentDialog()
      selectedSaleErrorMessage.value = 'This sale is no longer available.'
      return
    }

    paymentUpdateErrorMessage.value = getApiErrorMessage(error, 'Unable to update this payment summary right now.')
  } finally {
    isUpdatingPayment.value = false
  }
}

function clearSelectedSale() {
  selectedSaleId.value = ''
  selectedSale.value = null
  selectedSaleItems.value = []
}

function patchSaleInList(updatedSale) {
  sales.value = sales.value.map((sale) => {
    return sale.id === updatedSale.id ? updatedSale : sale
  })
}

function removeSaleFromList(saleId) {
  sales.value = sales.value.filter((sale) => sale.id !== saleId)
}

function formatMoney(value) {
  const amount = Number.parseFloat(String(value ?? 0))

  return Number.isFinite(amount) ? amount.toFixed(2) : '0.00'
}

function formatQuantity(value) {
  const quantity = Number.parseFloat(String(value ?? 0))

  if (!Number.isFinite(quantity)) {
    return '0'
  }

  return quantity.toFixed(3).replace(/\.?0+$/, '')
}

function formatDateTime(value) {
  if (!value) {
    return '-'
  }

  const parsedDate = new Date(String(value).replace(' ', 'T'))

  if (Number.isNaN(parsedDate.getTime())) {
    return String(value)
  }

  return parsedDate.toLocaleString()
}

function parseNonNegativeAmount(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue : fallback
}

function makeLocalDateTimeString(date = new Date()) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  const seconds = String(date.getSeconds()).padStart(2, '0')

  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}
</script>
