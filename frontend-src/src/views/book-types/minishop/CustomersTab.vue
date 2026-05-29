<template>
  <section class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <button type="button" class="btn btn-primary-subtle" @click="openCreateCustomerDialog">
            {{ $t('minishop.customers.addCustomer') }}
          </button>
        </div>
        <div class="flex-grow">
          <input
            v-model.trim="customerSearchQuery"
            type="search"
            :placeholder="$t('minishop.customers.searchCustomers')"
            class="form-control"
            @input="handleCustomerSearchInput"
          >
        </div>
      </header>

      <div class="flex-1 overflow-y-auto">
        
        <p v-if="customerErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ customerErrorMessage }}
        </p>

        <div v-else-if="isLoadingCustomers" class="p-4">
          <div class="skeleton">
                  <b> {{ $t('minishop.customers.loadingCustomers') }} </b> <b>Wait...</b>  <b> ... </b> 
          </div>
        </div>

        <p v-else-if="customerList.length === 0" class="px-4 py-4 text-secondary">
          {{ $t('minishop.customers.noCustomers') }}
        </p>

        <ul v-else class="mt-1">
          <li
            v-for="customer in customerList"
            :key="customer.id"
            class="border-bottom hover:bg-neutral-100"
          >
            <div
              role="button"
              class="px-4 d-flex p-3"
              :class="{ 'bg-primary-200': selectedCustomerId === customer.id }"
              @click="selectCustomer(customer)"
            >

              <div class="mr-3 avatar bg-neutral-200 text-base">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> 
              </div>

              <div class="d-flex flex-grow  justify-content-between gap-3">

                <div class="overflow-hidden">
                  <h6 class="mb-1 text-capitalize">{{ customer.name }} </h6>
                  <p class="mb-1" v-if="customer.phone"> 📞 {{ customer.phone }} </p>  
                  <p v-if="customer.note" class="text-secondary">
                    {{ customer.note }}
                  </p>
                </div>

                <div class="text-right flex-shrink-0">
                  <p>
                    🧾 {{ $t('minishop.customers.receiptCount', { count: formatQuantityDisplay(customer.receipt_count) }) }}
                  </p>
                  <p class="mt-1 text-secondary" :class="{ 'text-red': Number(customer.outstanding_balance) > 0 }">
                  {{ $t('minishop.customers.outstandingDebt') }}:  {{ formatMoney(customer.outstanding_balance) }} <small class="currency-code">{{ props.book.currency_code }}</small>
                  </p>
                  
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </aside>

    <aside class="col-6 mobile:max-w-full flex-grow mobile:w-full">
      <section class="flex-1 overflow-y-auto p-4 bg-neutral-100 h-full">
        <div v-if="selectedCustomerErrorMessage" class="alert alert-danger mb-2" role="alert">
          {{ selectedCustomerErrorMessage }}
        </div>

        <div v-if="isLoadingSelectedCustomer" class="card">
          <div class="p-10 text-secondary">{{ $t('minishop.customers.loadingCustomer') }}</div>
        </div>

        <article v-else-if="selectedCustomer" class="card shadow mobile:absolute mobile:top-0 mobile:left-0 mobile:bottom-0 mobile:right-0 z-40">
          <div class="card-body">
            
            <div class="d-flex align-items-start gap-3 mb-4 mobile:flex-col">
              <div class="d-flex flex-grow align-items-start">
                
                <div class="mr-3 avatar avatar-lg text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> 
                </div>
                
                <div>
                  <h3 class="text-2xl text-capitalize">{{ selectedCustomer.name }}</h3>
                  <p class="text-secondary text-xs mb-2"> ID: {{ selectedCustomer.id }}</p>

                  <p class="mb-1">
                    <span>📞 {{ $t('common.fields.phone') }}: </span>
                    <strong class="text-right">{{ selectedCustomer.phone || '-' }}</strong>
                  </p>
                  <p>
                    <span>💬 {{ $t('common.fields.note') }}: </span>
                    <span>{{ selectedCustomer.note }}</span>
                  </p>

                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="button" class="btn btn-icon btn-default" @click="openEditCustomerDialog">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                </button>
                <button type="button" class="btn btn-neutral" @click="clearSelectedCustomer">
                  {{ $t('common.actions.close') }}
                </button>
              </div>
            </div>

            <article class="row row-cols-4 mobile:row-cols-2 gap-2 mb-4">
              <div>
                <div class="border rounded bg-lower p-2">
                  <p class="text-secondary mb-1">{{ $t('minishop.customers.receipts') }}</p>
                  <strong class="text-lg">{{ formatQuantityDisplay(selectedCustomer.receipt_count) }} 🧾 </strong>
                </div>
              </div>
              <div>
                <div class="border rounded bg-lower p-2">
                  <p class="text-secondary mb-1">{{ $t('minishop.customers.outstandingDebt') }}</p>
                  <strong class="text-lg"  :class="{ 'text-red': Number(selectedCustomer.outstanding_balance) > 0 }"
                  >
                    {{ formatMoney(selectedCustomer.outstanding_balance) }} <small class="currency-code">{{ props.book.currency_code }}</small>
                  </strong>
                </div>
              </div>
              <div>
                <div class="border rounded bg-lower p-2">
                  <p class="text-secondary mb-1">{{ $t('minishop.customers.totalSales') }}</p>
                  <strong class="text-lg">{{ formatMoney(selectedCustomer.total_sales_amount) }} <small class="currency-code">{{ props.book.currency_code }}</small></strong>
                </div>
              </div>
              <div>
                <div class="border rounded bg-lower p-2">
                  <p class="text-secondary mb-1">{{ $t('minishop.customers.totalPaid') }}</p>
                  <strong class="text-lg">{{ formatMoney(selectedCustomer.total_paid_amount) }} <small class="currency-code">{{ props.book.currency_code }}</small></strong>
                </div>
              </div>
            </article>

            <p class="text-secondary text-sm">
              <span>{{ $t('common.fields.created') }}: </span>
              <span class="mr-2">{{ formatDateTime(selectedCustomer.created_at) }}</span>
              •
              <span>{{ $t('common.fields.updated') }}: </span>
              <span>{{ formatDateTime(selectedCustomer.updated_at) }}</span>
            </p>
            
            
            <hr>

            <article>
              <header class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <h4 class="text-lg">
                  {{ $t('minishop.customers.relatedReceipts') }}  ({{ selectedCustomerReceipts.length }})
                </h4>
              </header>

              <div v-if="selectedCustomerReceipts.length === 0" class="text-secondary">
                {{ $t('minishop.customers.noReceipts') }}
              </div>

              <ul v-else class="table-responsive">

                <li v-for="receipt in selectedCustomerReceipts" :key="receipt.id">

                  <div role="button" class="d-flex align-items-center p-3 bg-neutral-100 mb-1 hover:bg-neutral-200 border-strong rounded"
                        :disabled="isLoadingReceiptDetail && activeReceiptId === receipt.id"
                        @click="openReceiptDetailDialog(receipt.id)" 
                  >
                    <div class="mr-3">
                      <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                    </div>
                    <div>
                      <h6 class="mb-1"> {{ $t('common.fields.soldAt') }}:  {{ formatDateTime(receipt.sold_at) }}</h6>
                      <div class="d-flex gap-2">
                        <p> {{ $t('minishop.main.total') }}: {{ formatMoney(receipt.total_amount) }} <small class="currency-code">{{ receipt.currency_code }}</small></p> • 
                        <p> 💵 {{ $t('minishop.paymentLabels.' + receipt.payment_status) }}</p> • 
                        <p v-if="Number(receipt.due_amount) > 0" class="text-red"> {{ $t('minishop.sales.due') }}: {{ formatMoney(receipt.due_amount) }} <small class="currency-code">{{ receipt.currency_code }}</small></p> 
                      </div>
                      <p v-if="receipt.note" class="text-secondary">{{ receipt.note }}</p>
                    </div>
                  </div>
                </li>
              </ul>

            </article>
          </div>
          <!-- card-body .//end -->
        </article>
        <!-- card .//end -->
        
        <article v-else>

          <div class="p-10 text-center">
            <p class="text-lg">  👈 {{ $t('minishop.customers.selectCustomer') }}</p>
            <p class="text-secondary">  {{ $t('minishop.customers.selectCustomerHint') }} </p>
          </div>

          <hr>
        </article>


      </section>
    </aside>

    <CustomersCreateDialog
      ref="createCustomerDialog"
      :error-message="createCustomerErrorMessage"
      :form="createCustomerForm"
      :is-submit-disabled="isCreateCustomerSubmitDisabled"
      :is-submitting="isCreatingCustomer"
      @cancel="handleCreateCustomerDialogCancel"
      @close="handleCreateCustomerDialogClose"
      @submit="handleCreateCustomer"
    />

    <EditCustomerDialog
      ref="editCustomerDialog"
      :error-message="editCustomerErrorMessage"
      :form="editCustomerForm"
      :is-submit-disabled="isEditCustomerSubmitDisabled"
      :is-updating-customer="isUpdatingCustomer"
      @cancel="handleEditCustomerDialogCancel"
      @close="handleEditCustomerDialogClose"
      @submit="handleUpdateCustomer"
    />

    <ReceiptDetailDialog
      ref="receiptDetailDialog"
      :book="book"
      :can-add-payment-to-receipt="canAddPaymentToReceipt"
      :deleting-payment-id="deletingPaymentId"
      :error-message="receiptDetailErrorMessage"
      :is-deleting-receipt="isDeletingReceipt"
      :is-loading-receipt-detail="isLoadingReceiptDetail"
      :is-saving-payment="isSavingPayment"
      :items="selectedReceiptItems"
      :payments="selectedReceiptPayments"
      :sale="selectedReceiptSale"
      @cancel="handleReceiptDetailDialogCancel"
      @close="handleReceiptDetailDialogClose"
      @delete-payment="handleDeletePayment"
      @delete-receipt="handleDeleteReceipt"
      @open-add-payment="openAddPaymentDialog"
    />

    <AddPaymentDialog
      ref="addPaymentDialog"
      :book="book"
      dialog-class="dialog-sm"
      discount-input-id="customer-receipt-payment-discount"
      :error-message="paymentErrorMessage"
      :is-saving="isSavingPayment"
      paid-input-id="customer-receipt-payment-paid"
      payment-method-name="customer-receipt-payment-method"
      :sale="selectedReceiptSale"
      @cancel="handleAddPaymentDialogCancel"
      @close="handleAddPaymentDialogClose"
      @submit="handleAddPayment"
    />
  </section>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage, isNotFoundError, isUnauthorizedError } from '@/api/errors'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay } from '@/utils/quantity'
import {
  createMinishopCustomer,
  createMinishopSalePayment,
  deleteMinishopSale,
  deleteMinishopSalePayment,
  fetchMinishopCustomer,
  fetchMinishopCustomersListData,
  fetchMinishopSale,
  updateMinishopCustomer,
} from '@/api/minishop'
import AddPaymentDialog from '@/views/book-types/minishop/dialogs/AddPaymentDialog.vue'
import CustomersCreateDialog from '@/views/book-types/minishop/dialogs/CustomersCreateDialog.vue'
import EditCustomerDialog from '@/views/book-types/minishop/dialogs/EditCustomerDialog.vue'
import ReceiptDetailDialog from '@/views/book-types/minishop/dialogs/ReceiptDetailDialog.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['customers-changed'])

const router = useRouter()
const { t } = useI18n()

const createCustomerDialog = ref(null)
const editCustomerDialog = ref(null)
const receiptDetailDialog = ref(null)
const addPaymentDialog = ref(null)
const customerList = ref([])
const customerErrorMessage = ref('')
const customerSearchQuery = ref('')
const selectedCustomerId = ref('')
const selectedCustomer = ref(null)
const selectedCustomerReceipts = ref([])
const selectedReceiptSale = ref(null)
const selectedReceiptItems = ref([])
const selectedReceiptPayments = ref([])
const isLoadingCustomers = ref(false)
const isLoadingSelectedCustomer = ref(false)
const isLoadingReceiptDetail = ref(false)
const isCreatingCustomer = ref(false)
const isUpdatingCustomer = ref(false)
const isSavingPayment = ref(false)
const isDeletingReceipt = ref(false)
const selectedCustomerErrorMessage = ref('')
const receiptDetailErrorMessage = ref('')
const paymentErrorMessage = ref('')
const createCustomerErrorMessage = ref('')
const editCustomerErrorMessage = ref('')
const editingCustomerId = ref('')
const activeReceiptId = ref('')
const pendingFocusedCustomerId = ref('')
const deletingPaymentId = ref('')
let customerSearchDebounceTimer = null

const createCustomerForm = reactive({
  name: '',
  phone: '',
  note: '',
})

const editCustomerForm = reactive({
  name: '',
  phone: '',
  note: '',
})

const isCreateCustomerSubmitDisabled = computed(() => {
  return isCreatingCustomer.value || createCustomerForm.name === ''
})

const isEditCustomerSubmitDisabled = computed(() => {
  return isUpdatingCustomer.value || editingCustomerId.value === '' || editCustomerForm.name === ''
})

const canAddPaymentToReceipt = computed(() => {
  if (!selectedReceiptSale.value) {
    return false
  }

  return Number(selectedReceiptSale.value.due_amount) > 0
    || ['unpaid', 'partial'].includes(String(selectedReceiptSale.value.payment_status ?? ''))
})

watch(() => props.book.id, () => {
  clearSelectedCustomer()
  customerSearchQuery.value = ''
  void loadCustomerList('')
}, { immediate: true })

async function loadCustomerList(search = customerSearchQuery.value.trim()) {
  isLoadingCustomers.value = true
  customerErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCustomersListData(props.book.id, { search })
    customerList.value = sortCustomers(data.customers ?? [])
    syncSelectedCustomerSummary()

    if (pendingFocusedCustomerId.value !== '') {
      const focusedCustomer = customerList.value.find((customer) => customer.id === pendingFocusedCustomerId.value) ?? null

      if (focusedCustomer) {
        pendingFocusedCustomerId.value = ''
        await selectCustomer(focusedCustomer)
      }
    }

    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateCustomerDialog()
      closeEditCustomerDialog()
      await router.replace({ name: 'login' })
      return false
    }

    customerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableLoadCustomers'))
    return false
  } finally {
    isLoadingCustomers.value = false
  }
}

function syncSelectedCustomerSummary() {
  if (selectedCustomerId.value === '') {
    return
  }

  const matchingCustomer = customerList.value.find((customer) => customer.id === selectedCustomerId.value) ?? null

  if (matchingCustomer === null) {
    clearSelectedCustomer()
    return
  }

  if (selectedCustomer.value) {
    selectedCustomer.value = {
      ...selectedCustomer.value,
      ...matchingCustomer,
    }
  }
}

function handleCustomerSearchInput() {
  if (customerSearchDebounceTimer !== null) {
    window.clearTimeout(customerSearchDebounceTimer)
  }

  customerSearchDebounceTimer = window.setTimeout(() => {
    void loadCustomerList(customerSearchQuery.value.trim())
  }, 500)
}

async function selectCustomer(customer) {
  selectedCustomerId.value = customer.id
  selectedCustomer.value = {
    ...customer,
  }
  selectedCustomerReceipts.value = []
  await loadCustomerDetails(customer.id)
}

async function loadCustomerDetails(customerId) {
  isLoadingSelectedCustomer.value = true
  selectedCustomerErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCustomer(props.book.id, customerId)
    selectedCustomer.value = data.customer ?? null
    selectedCustomerReceipts.value = Array.isArray(data.receipts) ? data.receipts : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    clearSelectedCustomer()
    selectedCustomerErrorMessage.value = isNotFoundError(error)
      ? t('minishop.customers.customerUnavailable')
      : getApiErrorMessage(error, t('minishop.customers.unableLoadCustomer'))
  } finally {
    isLoadingSelectedCustomer.value = false
  }
}

function clearSelectedCustomer() {
  selectedCustomerId.value = ''
  selectedCustomer.value = null
  selectedCustomerReceipts.value = []
  selectedCustomerErrorMessage.value = ''
  isLoadingSelectedCustomer.value = false
  closeReceiptDetailDialog()
  closeAddPaymentDialog()
  resetSelectedReceiptState()
}

function openCreateCustomerDialog() {
  createCustomerErrorMessage.value = ''
  createCustomerDialog.value?.open()
}

function openEditCustomerDialog() {
  if (!selectedCustomer.value) {
    return
  }

  editCustomerErrorMessage.value = ''
  editingCustomerId.value = String(selectedCustomer.value.id ?? '')
  editCustomerForm.name = String(selectedCustomer.value.name ?? '')
  editCustomerForm.phone = String(selectedCustomer.value.phone ?? '')
  editCustomerForm.note = String(selectedCustomer.value.note ?? '')

  editCustomerDialog.value?.open()
}

function closeCreateCustomerDialog() {
  createCustomerDialog.value?.close()
}

function closeEditCustomerDialog() {
  editCustomerDialog.value?.close()
}

function openReceiptDetailDialog(receiptId) {
  void loadReceiptSale(receiptId)
}

function closeReceiptDetailDialog() {
  receiptDetailDialog.value?.close()
}

function openAddPaymentDialog() {
  if (!selectedReceiptSale.value) {
    return
  }

  paymentErrorMessage.value = ''
  addPaymentDialog.value?.open()
}

function closeAddPaymentDialog() {
  addPaymentDialog.value?.close()
}

function handleReceiptDetailDialogCancel(event) {
  if (isLoadingReceiptDetail.value) {
    event.preventDefault()
  }
}

function handleReceiptDetailDialogClose() {
  receiptDetailErrorMessage.value = ''
  isLoadingReceiptDetail.value = false
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

async function handleCreateCustomer() {
  if (isCreateCustomerSubmitDisabled.value) {
    return
  }

  createCustomerErrorMessage.value = ''
  isCreatingCustomer.value = true

  try {
    const { data } = await createMinishopCustomer(props.book.id, {
      name: createCustomerForm.name,
      phone: normalizeOptionalInput(createCustomerForm.phone),
      note: normalizeOptionalInput(createCustomerForm.note),
    })

    if (!data.customer) {
      throw new Error(t('minishop.dialogs.customerResponseMissing'))
    }

    customerSearchQuery.value = ''
    pendingFocusedCustomerId.value = data.customer.id
    closeCreateCustomerDialog()
    await loadCustomerList('')
    emit('customers-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateCustomerDialog()
      await router.replace({ name: 'login' })
      return
    }

    createCustomerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableCreateCustomer'))
  } finally {
    isCreatingCustomer.value = false
  }
}

async function handleUpdateCustomer() {
  if (isEditCustomerSubmitDisabled.value) {
    return
  }

  editCustomerErrorMessage.value = ''
  isUpdatingCustomer.value = true

  try {
    const { data } = await updateMinishopCustomer(props.book.id, editingCustomerId.value, {
      name: editCustomerForm.name,
      phone: normalizeOptionalInput(editCustomerForm.phone),
      note: normalizeOptionalInput(editCustomerForm.note),
    })

    if (!data.customer) {
      throw new Error(t('minishop.dialogs.customerResponseMissing'))
    }

    customerSearchQuery.value = ''
    pendingFocusedCustomerId.value = data.customer.id
    closeEditCustomerDialog()
    await loadCustomerList('')
    emit('customers-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeEditCustomerDialog()
      await router.replace({ name: 'login' })
      return
    }

    editCustomerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableUpdateCustomer'))
  } finally {
    isUpdatingCustomer.value = false
  }
}

async function loadReceiptSale(receiptId) {
  activeReceiptId.value = receiptId
  receiptDetailErrorMessage.value = ''
  isLoadingReceiptDetail.value = true

  try {
    const { data } = await fetchMinishopSale(props.book.id, receiptId)
    selectedReceiptSale.value = data.sale ?? null
    selectedReceiptItems.value = Array.isArray(data.items) ? data.items : []
    selectedReceiptPayments.value = Array.isArray(data.payments) ? data.payments : []

    if (!selectedReceiptSale.value) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    receiptDetailDialog.value?.open()
  } catch (error) {
    const didHandle = await handleReceiptActionError(error, receiptId)

    if (!didHandle) {
      const message = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
        ? error.message
        : getApiErrorMessage(error, t('minishop.customers.unableLoadReceipt'))

      receiptDetailErrorMessage.value = message
      selectedCustomerErrorMessage.value = message
    }
  } finally {
    isLoadingReceiptDetail.value = false
  }
}

async function handleAddPayment(paymentPayload) {
  if (!selectedReceiptSale.value || isSavingPayment.value || !paymentPayload || paymentPayload.amount <= 0) {
    return
  }

  paymentErrorMessage.value = ''
  isSavingPayment.value = true
  const receiptId = selectedReceiptSale.value.id

  try {
    const { data } = await createMinishopSalePayment(props.book.id, receiptId, {
      discount_amount: paymentPayload.discount_amount,
      payment_method: paymentPayload.payment_method,
      amount: paymentPayload.amount,
      paid_at: makeLocalDateTimeString(),
    })

    if (!data.sale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    selectedReceiptSale.value = data.sale
    selectedReceiptPayments.value = Array.isArray(data.payments) ? data.payments : []
    closeAddPaymentDialog()
    await refreshSelectedCustomerData()
    emit('customers-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeAddPaymentDialog()
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      closeAddPaymentDialog()
      await handleReceiptActionError(error, receiptId)
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
  if (!selectedReceiptSale.value || deletingPaymentId.value !== '') {
    return
  }

  if (!window.confirm(`${t('minishop.sales.deletePayment')}?`)) {
    return
  }

  receiptDetailErrorMessage.value = ''
  deletingPaymentId.value = payment.id
  const receiptId = selectedReceiptSale.value.id

  try {
    const { data } = await deleteMinishopSalePayment(props.book.id, receiptId, payment.id)

    if (!data.sale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    selectedReceiptSale.value = data.sale
    selectedReceiptPayments.value = Array.isArray(data.payments) ? data.payments : []
    await refreshSelectedCustomerData()
    emit('customers-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      await handleReceiptActionError(error, receiptId)
      return
    }

    receiptDetailErrorMessage.value = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
      ? error.message
      : getApiErrorMessage(error, t('minishop.sales.unableDeletePayment'))
  } finally {
    deletingPaymentId.value = ''
  }
}

async function handleDeleteReceipt() {
  if (!selectedReceiptSale.value || isDeletingReceipt.value || isSavingPayment.value) {
    return
  }

  if (selectedReceiptPayments.value.length > 0) {
    const message = t('minishop.sales.deletePaymentsFirst')
    receiptDetailErrorMessage.value = message
    window.alert(message)
    return
  }

  const receiptId = selectedReceiptSale.value.id

  if (!window.confirm(t('minishop.sales.confirmDeleteSale'))) {
    return
  }

  receiptDetailErrorMessage.value = ''
  isDeletingReceipt.value = true

  try {
    await deleteMinishopSale(props.book.id, receiptId)
    closeReceiptDetailDialog()
    resetSelectedReceiptState()
    await refreshSelectedCustomerData()
    emit('customers-changed')
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeReceiptDetailDialog()
      await router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      await handleReceiptActionError(error, receiptId)
      return
    }

    receiptDetailErrorMessage.value = getApiErrorMessage(error, t('minishop.sales.unableDeleteSale'))
  } finally {
    isDeletingReceipt.value = false
  }
}

function handleCreateCustomerDialogClose() {
  resetCreateCustomerForm()
}

function handleCreateCustomerDialogCancel(event) {
  if (isCreatingCustomer.value) {
    event.preventDefault()
  }
}

function handleEditCustomerDialogClose() {
  resetEditCustomerForm()
}

function handleEditCustomerDialogCancel(event) {
  if (isUpdatingCustomer.value) {
    event.preventDefault()
  }
}

async function refreshSelectedCustomerData() {
  const currentCustomerId = selectedCustomerId.value

  await loadCustomerList(customerSearchQuery.value.trim())

  if (currentCustomerId !== '') {
    await loadCustomerDetails(currentCustomerId)
  }
}

async function handleReceiptActionError(error, receiptId) {
  if (isUnauthorizedError(error)) {
    closeReceiptDetailDialog()
    closeAddPaymentDialog()
    await router.replace({ name: 'login' })
    return true
  }

  if (isNotFoundError(error)) {
    closeReceiptDetailDialog()
    closeAddPaymentDialog()

    if (activeReceiptId.value === receiptId) {
      resetSelectedReceiptState()
    }

    selectedCustomerErrorMessage.value = t('minishop.customers.saleUnavailable')
    await refreshSelectedCustomerData()
    emit('customers-changed')
    return true
  }

  return false
}

function resetSelectedReceiptState() {
  activeReceiptId.value = ''
  selectedReceiptSale.value = null
  selectedReceiptItems.value = []
  selectedReceiptPayments.value = []
  receiptDetailErrorMessage.value = ''
  paymentErrorMessage.value = ''
  deletingPaymentId.value = ''
  isDeletingReceipt.value = false
}

function resetCreateCustomerForm() {
  createCustomerForm.name = ''
  createCustomerForm.phone = ''
  createCustomerForm.note = ''
  createCustomerErrorMessage.value = ''
  isCreatingCustomer.value = false
}

function resetEditCustomerForm() {
  editCustomerForm.name = ''
  editCustomerForm.phone = ''
  editCustomerForm.note = ''
  editCustomerErrorMessage.value = ''
  editingCustomerId.value = ''
  isUpdatingCustomer.value = false
}

function normalizeOptionalInput(value) {
  const normalizedValue = String(value ?? '').trim()

  return normalizedValue === '' ? null : normalizedValue
}

function sortCustomers(items) {
  return [...items].sort((leftCustomer, rightCustomer) => {
    return String(leftCustomer.name ?? '').localeCompare(String(rightCustomer.name ?? ''))
  })
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
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
