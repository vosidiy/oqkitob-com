<template>
  <section class="d-flex flex-1 overflow-hidden mobile:flex-col">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <select
            name="filter_time"
            class="form-select"
            :value="selectedFilterTime"
            @change="selectedFilterTime = $event.target.value"
          >
            <option value="today">{{ $t('minishop.sales.filters.today') }}</option>
            <option value="yesterday">{{ $t('minishop.sales.filters.yesterday') }}</option>
            <option value="last_10_days">{{ $t('minishop.sales.filters.last10Days') }}</option>
            <option value="last_20_days">{{ $t('minishop.sales.filters.last20Days') }}</option>
            <option value="last_30_days">{{ $t('minishop.sales.filters.last30Days') }}</option>
            <option value="previous_month">{{ $t('minishop.sales.filters.previousMonth') }}</option>
            <option value="this_year">{{ $t('minishop.sales.filters.thisYear') }}</option>
            <option value="all_time">{{ $t('minishop.sales.filters.allTime') }}</option>
          </select>
        </div>
        <div class="flex-grow">
          <input
            v-model.trim="salesSearchQuery"
            type="search"
            :placeholder="$t('minishop.sales.searchSales')"
            class="form-control"
            @input="handleSalesSearchInput"
          >
        </div>
      </header>

      <section class="flex-1 overflow-y-auto">

        <div class="p-4">
          <RouterLink :to="{ name: 'book-detail', params: { bookId: props.book.id } }" class="btn w-full btn-default">
              {{ $t('minishop.sales.createSale') }}
          </RouterLink>
        </div>

        <div v-if="salesListErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ salesListErrorMessage }}
        </div>
        
        <div v-if="isLoadingSalesList" class="p-4 text-secondary">
          {{ $t('minishop.sales.loadingSales') }}
        </div>

        <div v-else-if="sales.length === 0" class="p-4 text-secondary">
          <p class="mb-0 text-lg text-center">
            {{ hasActiveSalesSearch ? $t('minishop.sales.noSearchResults') : $t('minishop.sales.noSales') }}
          </p>
          <div v-if="hasActiveSalesSearch" class="d-flex gap-2 mt-3 mobile:flex-col">
            <button
              v-if="shouldShowAllPeriodsAction"
              type="button"
              class="btn btn-primary"
              @click="showAllPeriods"
            >
              {{ $t('minishop.sales.showAllPeriods') }}
            </button>
            <button
              type="button"
              class="btn btn-default"
              @click="clearSalesSearch"
            >
              {{ $t('minishop.sales.clearSearch') }}
            </button>
          </div>
        </div>

        <ul v-else class="mt-1">
          <li v-for="sale in sales" :key="sale.id" class="border-bottom hover:bg-neutral-100">
            <a href="#" role="button" class="px-4 p-3 text-base d-block"
              :class="{ 'bg-primary-200': selectedSaleId === sale.id }"
              @click="selectSale(sale)"
            >
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                </div>
                <div>
                  <h6>{{ formatDateTime(sale.sold_at) }}</h6>
                  <small class="text-sm text-secondary">ID: {{ sale.id }}</small>
                  <p v-if="sale.customer_name" class="mt-1 mb-0">
                    👤 {{ sale.customer_name }}
                    <span class="text-secondary" v-if="sale.customer_phone">  • 📞 {{ sale.customer_phone }}</span>
                  </p>
                  <p v-if="sale.note" class="text-sm text-secondary mt-1 mb-0">
                    {{ sale.note }}
                  </p>
                </div>
                <div class="ml-auto text-right">
                  <p class="font-semibold">{{ formatMoney(sale.total_amount) }}</p>
                  <p v-if="Number(sale.due_amount) > 0" class="text-red">
                    {{ $t('minishop.sales.due') }}: {{ formatMoney(sale.due_amount) }}
                  </p>
                  <span class="text-secondary text-sm"> Status: {{ $t('minishop.paymentLabels.' + sale.payment_status) }}</span>
                </div>
              </div>
            </a>
          </li>
        </ul>
      </section>
    </aside>

    <aside class="col-6">
      <section class="flex-1 overflow-y-auto p-4 bg-lower h-full">
        <div v-if="selectedSaleErrorMessage" class="alert alert-danger" role="alert">
          {{ selectedSaleErrorMessage }}
        </div>

        <article v-if="isLoadingSelectedSale" class="card">
          <div class="p-10 text-center text-secondary text-lg">{{ $t('minishop.sales.loadingReceipt') }}</div>
        </article>

        <article v-else-if="selectedSale" class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4 mobile:flex-col">
              <div>
                <h3 class="text-2xl mb-1">{{ $t('minishop.sales.receipt') }}</h3>
                <p class="text-secondary mb-0">{{ selectedSale.id }}</p>
              </div>

              <div class="text-right mobile:text-left">
                <p class="mb-1"><strong>{{ $t('common.fields.soldAt') }}:</strong> {{ formatDateTime(selectedSale.sold_at) }}</p>
                <p class="mb-1"><strong>{{ $t('common.fields.currency') }}:</strong> {{ selectedSale.currency_code }}</p>
                <p class="mb-1">
                  <strong>{{ $t('common.fields.customer') }}:</strong>
                  {{ selectedSale.customer_name || $t('minishop.sales.noCustomer') }}
                  <span v-if="selectedSale.customer_phone"> · {{ selectedSale.customer_phone }}</span>
                </p>
                <p class="mb-3 text-capitalize"><strong>{{ $t('common.fields.status') }}:</strong> {{ $t('minishop.paymentLabels.' + selectedSale.payment_status) }}</p>
                <button
                  type="button"
                  class="btn btn-default mr-2"
                  :disabled="isDeletingSelectedSale"
                  @click="clearSelectedSale"
                >
                  {{ $t('common.actions.close') }}
                </button>
                <button
                  type="button"
                  class="btn btn-outline text-red"
                  :disabled="isDeletingSelectedSale"
                  @click="handleDeleteSelectedSale"
                >
                  <span v-if="isDeletingSelectedSale">{{ $t('common.states.deleting') }}</span>
                  <span v-else>{{ $t('minishop.sales.deleteSale') }}</span>
                </button>
              </div>
            </div>

            <div class="table-responsive mb-4">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>{{ $t('common.fields.item') }}</th>
                    <th>{{ $t('minishop.main.quantityShort') }}</th>
                    <th>{{ $t('common.fields.price') }}</th>
                    <th>{{ $t('common.fields.total') }}</th>
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
                v-if="selectedSale.customer_name"
                class="d-flex justify-content-between gap-3"
              >
                <span>{{ $t('common.fields.customer') }}</span>
                <strong class="text-right">
                  {{ selectedSale.customer_name }}
                  <span v-if="selectedSale.customer_phone"> · {{ selectedSale.customer_phone }}</span>
                </strong>
              </div>
              <div
                v-if="selectedSale.note"
                class="d-flex justify-content-between gap-3"
              >
                <span>{{ $t('common.fields.note') }}</span>
                <strong class="text-right">{{ selectedSale.note }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.subtotal') }}</span>
                <strong>{{ formatMoney(selectedSale.subtotal_amount) }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.discount') }}</span>
                <strong>- {{ formatMoney(selectedSale.discount_amount) }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.total') }}</span>
                <strong>{{ formatMoney(selectedSale.total_amount) }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.paid') }}</span>
                <strong>{{ formatMoney(selectedSale.paid_amount) }}</strong>
              </div>
              <div
                v-if="Number(selectedSale.due_amount) > 0"
                class="d-flex justify-content-between gap-3 text-orange"
              >
                <span>{{ $t('minishop.sales.remainingDebt') }}</span>
                <strong>{{ formatMoney(selectedSale.due_amount) }}</strong>
              </div>
              <div v-else class="d-flex justify-content-between gap-3 text-green">
                <span>{{ $t('common.fields.status') }}</span>
                <strong>{{ $t('common.states.paidInFull') }}</strong>
              </div>
            </div>

            <div class="mt-5 pt-4 border-top">
              <div class="d-flex justify-content-between align-items-center gap-3 mb-3 mobile:flex-col mobile:align-items-start">
                <h4 class="h6 mb-0">{{ $t('minishop.sales.paymentRecords') }}</h4>
                <button
                  type="button"
                  class="btn btn-primary"
                  :disabled="isDeletingSelectedSale || isSavingPayment"
                  @click="openAddPaymentDialog"
                >
                  {{ $t('minishop.sales.addPayment') }}
                </button>
              </div>

              <div v-if="selectedSalePayments.length === 0" class="text-secondary">
                {{ $t('minishop.sales.noPaymentRecords') }}
              </div>

              <div v-else class="d-flex flex-col gap-3">
                <div
                  v-for="payment in selectedSalePayments"
                  :key="payment.id"
                  class="border rounded p-3 bg-lower"
                >
                  <div class="d-flex justify-content-between gap-3 mobile:flex-col">
                    <div>
                      <p class="mb-1"><strong>{{ $t('common.fields.date') }}:</strong> {{ formatDateTime(payment.paid_at || payment.created_at) }}</p>
                      <p class="mb-0"><strong>{{ $t('common.fields.method') }}:</strong> {{ $t('minishop.paymentMethods.' + payment.payment_method) }}</p>
                    </div>
                    <div class="text-right mobile:text-left">
                      <p class="mb-2"><strong>{{ formatMoney(payment.amount) }}</strong></p>
                      <button
                        type="button"
                        class="btn btn-outline text-red"
                        :disabled="deletingPaymentId === payment.id || isSavingPayment || isDeletingSelectedSale"
                        @click="handleDeletePayment(payment)"
                      >
                        <span v-if="deletingPaymentId === payment.id">{{ $t('common.states.deleting') }}</span>
                        <span v-else>{{ $t('minishop.sales.deletePayment') }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </article>

        <article v-else>

          <p class="text-lg p-10 text-secondary text-center">
              👈  {{ $t('minishop.sales.selectSaleHint') }}
          </p>

          <hr>
          

          <div class="card card-body mb-4">
            <h6 class="text-lg mb-4">{{ salesSummaryTitle }}</h6>
            <ul class="d-grid gap-2 grid-template-cols-4 lh-sm">
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('minishop.sales.summaryCount') }}: </p>
                <strong>{{ salesSummaryCount }}</strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('minishop.sales.summaryTotalAmount') }}: </p>
                <strong>{{ formatMoney(salesSummaryTotalAmount) }}</strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('minishop.sales.summaryPaidAmount') }}: </p>
                <strong class="text-green">{{ formatMoney(salesSummaryPaidAmount) }}</strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('minishop.sales.summaryDueAmount') }}: </p>
                <strong class="text-orange">{{ formatMoney(salesSummaryDueAmount) }} </strong>
              </li>
            </ul>
          </div>

          <div class="card card-body">
            <h6 class="text-lg mb-4">By each products (Selected period: Today) </h6>
            A table that shows sales by each product number of sales and amount
          </div>

          
        </article>


      </section>
    </aside>
  </section>

  <AddPaymentDialog
    ref="addPaymentDialog"
    dialog-class="dialog-sm"
    discount-input-id="sales-add-payment-discount"
    :error-message="paymentErrorMessage"
    :is-saving="isSavingPayment"
    paid-input-id="sales-add-payment-paid"
    payment-method-name="sales-add-payment-method"
    :sale="selectedSale"
    @cancel="handleAddPaymentDialogCancel"
    @close="handleAddPaymentDialogClose"
    @submit="handleAddPayment"
  />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage, isNotFoundError, isUnauthorizedError } from '@/api/errors'
import {
  createMinishopSalePayment,
  deleteMinishopSale,
  deleteMinishopSalePayment,
  fetchMinishopSale,
  fetchMinishopSales,
} from '@/api/minishop'
import AddPaymentDialog from '@/views/book-types/minishop/dialogs/AddPaymentDialog.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits([
  'sales-changed',
])

const router = useRouter()
const { t } = useI18n()

const addPaymentDialog = ref(null)
const sales = ref([])
const selectedSaleId = ref('')
const selectedSale = ref(null)
const selectedSaleItems = ref([])
const selectedSalePayments = ref([])
const isLoadingSalesList = ref(false)
const isLoadingSelectedSale = ref(false)
const isDeletingSelectedSale = ref(false)
const isSavingPayment = ref(false)
const deletingPaymentId = ref('')
const salesListErrorMessage = ref('')
const selectedSaleErrorMessage = ref('')
const paymentErrorMessage = ref('')
const selectedFilterTime = ref('today')
const salesSearchQuery = ref('')

let salesSearchDebounceTimer = null
let latestSalesRequestId = 0

const salesFilterLabelKeys = {
  all_time: 'minishop.sales.filters.allTime',
  last_10_days: 'minishop.sales.filters.last10Days',
  last_20_days: 'minishop.sales.filters.last20Days',
  last_30_days: 'minishop.sales.filters.last30Days',
  previous_month: 'minishop.sales.filters.previousMonth',
  this_year: 'minishop.sales.filters.thisYear',
  today: 'minishop.sales.filters.today',
  yesterday: 'minishop.sales.filters.yesterday',
}

const salesSummaryCount = computed(() => sales.value.length)

const salesSummaryTotalAmount = computed(() => {
  return sumSaleAmounts('total_amount')
})

const salesSummaryPaidAmount = computed(() => {
  return sumSaleAmounts('paid_amount')
})

const salesSummaryDueAmount = computed(() => {
  return sumSaleAmounts('due_amount')
})

const salesSummaryTitle = computed(() => {
  return t('minishop.sales.summaryTitle', {
    filter: t(salesFilterLabelKeys[selectedFilterTime.value] ?? salesFilterLabelKeys.today),
  })
})

const hasActiveSalesSearch = computed(() => salesSearchQuery.value !== '')
const shouldShowAllPeriodsAction = computed(() => {
  return hasActiveSalesSearch.value && selectedFilterTime.value !== 'all_time'
})

watch(() => props.book.id, async () => {
  cancelPendingSalesSearch()
  salesSearchQuery.value = ''
  await loadSales('')
}, { immediate: true })

watch(selectedFilterTime, async () => {
  cancelPendingSalesSearch()
  await loadSales()
})

async function loadSales(search = salesSearchQuery.value) {
  const requestId = ++latestSalesRequestId

  isLoadingSalesList.value = true
  salesListErrorMessage.value = ''
  clearSelectedSale()
  selectedSaleErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopSales(props.book.id, {
      filter_time: selectedFilterTime.value,
      local_now: makeLocalDateTimeString(),
      search,
    })

    if (!isLatestSalesRequest(requestId)) {
      return
    }

    sales.value = Array.isArray(data.sales) ? data.sales : []
  } catch (error) {
    if (!isLatestSalesRequest(requestId)) {
      return
    }

    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    sales.value = []
    salesListErrorMessage.value = getApiErrorMessage(error, t('minishop.sales.unableLoadSales'))
  } finally {
    if (isLatestSalesRequest(requestId)) {
      isLoadingSalesList.value = false
    }
  }
}

function handleSalesSearchInput() {
  cancelPendingSalesSearch()

  salesSearchDebounceTimer = window.setTimeout(() => {
    salesSearchDebounceTimer = null
    void loadSales(salesSearchQuery.value)
  }, 500)
}

function clearSalesSearch() {
  cancelPendingSalesSearch()
  salesSearchQuery.value = ''
  void loadSales('')
}

function showAllPeriods() {
  if (selectedFilterTime.value === 'all_time') {
    return
  }

  cancelPendingSalesSearch()
  selectedFilterTime.value = 'all_time'
}

async function selectSale(sale) {
  selectedSaleId.value = sale.id
  selectedSaleErrorMessage.value = ''
  isLoadingSelectedSale.value = true

  try {
    const { data } = await fetchMinishopSale(props.book.id, sale.id)
    selectedSale.value = data.sale ?? null
    selectedSaleItems.value = Array.isArray(data.items) ? data.items : []
    selectedSalePayments.value = Array.isArray(data.payments) ? data.payments : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    clearSelectedSale()
    selectedSaleErrorMessage.value = isNotFoundError(error)
      ? t('minishop.sales.saleUnavailable')
      : getApiErrorMessage(error, t('minishop.sales.unableLoadReceipt'))

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

  if (selectedSalePayments.value.length > 0) {
    const message = t('minishop.sales.deletePaymentsFirst')
    selectedSaleErrorMessage.value = message
    window.alert(message)
    return
  }

  const saleId = selectedSale.value.id

  if (!window.confirm(t('minishop.sales.confirmDeleteSale'))) {
    return
  }

  selectedSaleErrorMessage.value = ''
  isDeletingSelectedSale.value = true

  try {
    const { data } = await deleteMinishopSale(props.book.id, saleId)
    removeSaleFromList(data.saleId ?? saleId)
    clearSelectedSale()
    emit('sales-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      removeSaleFromList(saleId)
      clearSelectedSale()
      selectedSaleErrorMessage.value = t('minishop.sales.saleUnavailable')
      return
    }

    selectedSaleErrorMessage.value = getApiErrorMessage(error, t('minishop.sales.unableDeleteSale'))
  } finally {
    isDeletingSelectedSale.value = false
  }
}

function openAddPaymentDialog() {
  if (!selectedSale.value) {
    return
  }

  paymentErrorMessage.value = ''
  addPaymentDialog.value?.open()
}

function closeAddPaymentDialog() {
  addPaymentDialog.value?.close()
}

function handleAddPaymentDialogCancel(event) {
  if (isSavingPayment.value) {
    event.preventDefault()
  }
}

function handleAddPaymentDialogClose() {
  paymentErrorMessage.value = ''
  isSavingPayment.value = false
}

async function handleAddPayment(paymentPayload) {
  if (!selectedSale.value || isSavingPayment.value || !paymentPayload || paymentPayload.amount <= 0) {
    return
  }

  paymentErrorMessage.value = ''
  isSavingPayment.value = true
  const saleId = selectedSale.value.id

  try {
    const { data } = await createMinishopSalePayment(props.book.id, saleId, {
      discount_amount: paymentPayload.discount_amount,
      payment_method: paymentPayload.payment_method,
      amount: paymentPayload.amount,
      paid_at: makeLocalDateTimeString(),
    })

    if (!data.sale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    selectedSale.value = data.sale
    selectedSalePayments.value = Array.isArray(data.payments) ? data.payments : []
    patchSaleInList(data.sale)
    closeAddPaymentDialog()
    emit('sales-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeAddPaymentDialog()
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      removeSaleFromList(saleId)
      clearSelectedSale()
      closeAddPaymentDialog()
      selectedSaleErrorMessage.value = t('minishop.sales.saleUnavailable')
      return
    }

    paymentErrorMessage.value = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
      ? error.message
      : getApiErrorMessage(error, t('minishop.sales.unableAddPayment'))
  } finally {
    isSavingPayment.value = false
  }
}

async function handleDeletePayment(payment) {
  if (!selectedSale.value || deletingPaymentId.value !== '') {
    return
  }

  if (!window.confirm(`${t('minishop.sales.deletePayment')}?`)) {
    return
  }

  selectedSaleErrorMessage.value = ''
  deletingPaymentId.value = payment.id
  const saleId = selectedSale.value.id

  try {
    const { data } = await deleteMinishopSalePayment(props.book.id, saleId, payment.id)

    if (!data.sale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    selectedSale.value = data.sale
    selectedSalePayments.value = Array.isArray(data.payments) ? data.payments : []
    patchSaleInList(data.sale)
    emit('sales-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      const matchingSale = sales.value.find((sale) => sale.id === saleId)

      if (matchingSale) {
        await selectSale(matchingSale)
      } else {
        clearSelectedSale()
      }

      return
    }

    selectedSaleErrorMessage.value = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
      ? error.message
      : getApiErrorMessage(error, t('minishop.sales.unableDeletePayment'))
  } finally {
    deletingPaymentId.value = ''
  }
}

function clearSelectedSale() {
  selectedSaleId.value = ''
  selectedSale.value = null
  selectedSaleItems.value = []
  selectedSalePayments.value = []
}

function patchSaleInList(updatedSale) {
  sales.value = sales.value.map((sale) => {
    return sale.id === updatedSale.id ? updatedSale : sale
  })
}

function removeSaleFromList(saleId) {
  sales.value = sales.value.filter((sale) => sale.id !== saleId)
}

function cancelPendingSalesSearch() {
  if (salesSearchDebounceTimer !== null) {
    window.clearTimeout(salesSearchDebounceTimer)
    salesSearchDebounceTimer = null
  }
}

function isLatestSalesRequest(requestId) {
  return requestId === latestSalesRequestId
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

function sumSaleAmounts(fieldName) {
  return sales.value.reduce((total, sale) => {
    return total + (Number(sale?.[fieldName]) || 0)
  }, 0)
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
