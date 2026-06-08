<template>
  <main class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <button type="button" class="btn btn-primary-subtle" @click="openCreateCustomerDialog">
            {{ $t('service.clients.addClient') }}
          </button>
        </div>
        <div class="flex-grow">
          <div class="relative">
            <input
              v-model.trim="customerSearchQuery"
              type="search"
              :placeholder="$t('service.clients.searchPlaceholder')"
              class="form-control"
              @input="handleCustomerSearchInput"
            >
            <button
              v-if="hasActiveCustomerSearch"
              type="button"
              class="btn btn-neutral top-0 right-0 absolute"
              title="Clear search"
              @click="clearCustomerSearch"
            >
              ✕
            </button>
          </div>
        </div>
      </header>

      <div class="flex-1 overflow-y-auto">
        <p v-if="customerErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ customerErrorMessage }}
        </p>

        <div v-else-if="isLoadingCustomers" class="p-4">
          <div class="skeleton">
            <b>{{ $t('service.clients.loadingClients') }}</b>
          </div>
        </div>

        <p v-else-if="customerList.length === 0" class="px-4 py-4 text-secondary">
          {{ $t('service.clients.noClients') }}
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
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </div>
              <div class="d-flex flex-grow align-items-center justify-content-between gap-3">
                <div>
                  <h6 class="mb-1 text-capitalize">{{ customer.name }}</h6>
                  <p class="mb-1">
                    📞 {{ customer.phone }}
                    <span v-if="customer.address"> • {{ customer.address }}</span>
                  </p>
                </div>
                <div class="text-right flex-shrink-0">
                  <p>{{ $t('service.clients.orderCount', { count: Number(customer.order_count ?? 0) }) }}</p>
                  <p class="mt-1 text-secondary">
                    {{ $t('service.clients.lastOrder') }}:
                    {{ customer.last_order_at ? formatDateTime(customer.last_order_at) : $t('service.clients.noLastOrder') }}
                  </p>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </aside>

    <aside class="col-6 mobile:max-w-full flex-grow mobile:w-full">
      <section class="flex-1 overflow-y-auto p-3 bg-neutral-100 h-full">
        
        <div v-if="feedbackMessage" class="toast" role="status">
           {{ feedbackMessage }}
        </div>

        <div v-if="selectedCustomerErrorMessage" class="alert alert-danger mb-4" role="alert">
          {{ selectedCustomerErrorMessage }}
        </div>

        <article v-if="isLoadingSelectedCustomer" class="card shadow">
          <div class="p-10 text-secondary">{{ $t('service.clients.loadingClientDetail') }}</div>
        </article>

        <article v-else-if="selectedCustomer" id="client_detail" class="card shadow">
          <div class="card-body">
            <div class="d-flex align-items-start gap-3 mb-4 mobile:flex-col">
              <div class="d-flex flex-grow align-items-start">
                <div class="mr-4 avatar avatar-lg text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-icon lucide-user">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </div>
                <div>
                  <h3 class="text-2xl mb-2 text-capitalize">{{ selectedCustomer.name }}</h3>

                  <p class="mb-1 text-lg">
                    <span>📞 {{ selectedCustomer.phone }}</span>
                    <span v-if="selectedCustomer.messenger">
                      <span class="px-2">•</span>
                      <span>💬 {{ $t('service.fields.messenger') }}: {{ selectedCustomer.messenger }}</span>
                    </span>
                  </p>
                  <p v-if="selectedCustomer.address" class="mb-1 text-lg">
                    <span>🏠 {{ $t('service.fields.address') }}: </span>
                    <span>{{ selectedCustomer.address }}</span>
                  </p>
                  <p v-if="selectedCustomer.location" class="mb-1 text-lg">
                    <span>📍 {{ $t('service.fields.location') }}: </span>
                    <a
                      v-if="looksLikeUrl(selectedCustomer.location)"
                      :href="selectedCustomer.location"
                      target="_blank"
                      rel="noreferrer"
                      class="link"
                    >
                      {{ selectedCustomer.location }}
                    </a>
                    <span v-else>{{ selectedCustomer.location }}</span>
                  </p>

                  <p class="text-secondary mt-4 text-sm">
                    <span>{{ $t('common.fields.created') }}: </span>
                    <span class="mr-2">{{ formatDateTime(selectedCustomer.created_at) }}</span>
                    •
                    <span>{{ $t('common.fields.updated') }}: </span>
                    <span>{{ formatDateTime(selectedCustomer.updated_at) }}</span>
                  </p>
                  <p class="text-secondary text-xs">ID: {{ selectedCustomer.id }}</p>
                </div>
              </div>

              <div class="d-flex gap-2">
                <button type="button" :title="$t('service.clients.editClient')" class="btn btn-icon btn-default" @click="openEditCustomerDialog">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil">
                    <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path>
                    <path d="m15 5 4 4"></path>
                  </svg>
                </button>
                <button type="button" class="btn btn-neutral" @click="clearSelectedCustomer">
                  {{ $t('common.actions.close') }}
                </button>
              </div>
            </div>

            <hr>

            <div>
              <header class="mb-4 d-flex justify-content-between align-items-center gap-3">
                <h4 class="text-lg">
                  {{ $t('service.clients.ordersTitle') }} ({{ selectedCustomerOrders.length }})
                </h4>
                <button type="button" class="btn btn-primary" @click="openCreateOrderDialogForCustomer">
                  {{ $t('service.clients.addOrder') }}
                </button>
              </header>

              <div v-if="serviceTypesErrorMessage" class="alert alert-danger mb-4" role="alert">
                {{ serviceTypesErrorMessage }}
              </div>

              <div v-if="selectedCustomerOrders.length === 0" class="text-secondary">
                {{ $t('service.clients.noOrders') }}
              </div>

              <ul v-else class="my-4">
                <li
                  v-for="order in selectedCustomerOrders"
                  :key="order.id"
                  class="d-flex align-items-center p-3 bg-neutral-100 mb-1 border-strong rounded hover:bg-neutral-200"
                  role="button"
                  :aria-disabled="isLoadingSelectedCustomerOrder && activeCustomerOrderId === order.id"
                  @click="openOrderDetailDialog(order.id)"
                >
                  <div class="mr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"></path></svg>
                  </div>
                  <div class="flex-grow">
                    <h6 class="mb-1">
                      {{ $t('service.orders.receivedAt') }}: {{ formatDateTime(order.received_at) }}
                    </h6>
                    <div class="d-flex gap-2 flex-wrap">
                      <p>{{ $t('service.orders.itemsCount', { count: Number(order.item_count ?? 0) }) }}</p>
                      <span>•</span>
                      <p>{{ formatMoney(order.total_amount) }} <small class="currency-code">{{ order.currency_code }}</small></p>
                      <span>•</span>
                      <p :class="getOrderStatusMeta(order.order_status).textClass">
                        {{ $t('common.fields.status') }}:
                        {{ getOrderStatusMeta(order.order_status).emoji }}
                        {{ $t(getOrderStatusMeta(order.order_status).labelKey) }}
                      </p>
                    </div>
                    <p v-if="order.note" class="text-secondary">{{ order.note }}</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </article>

        <article v-else>
          <div class="p-10 text-center">
            <p class="text-lg">👈 {{ $t('service.clients.selectClient') }}</p>
            <p class="text-secondary">{{ $t('service.clients.selectClientHint') }}</p>
          </div>
        </article>
      </section>
    </aside>
  </main>

  <ServiceClientDialog
    ref="customerDialogRef"
    :error-message="customerDialogErrorMessage"
    :form="customerForm"
    :is-submit-disabled="isCustomerSubmitDisabled"
    :is-submitting="isSubmittingCustomer"
    :submit-label="customerDialogSubmitLabel"
    :title="customerDialogTitle"
    @close="customerDialogErrorMessage = ''"
    @submit="submitCustomer"
  />

  <CreateServiceOrderDialog
    ref="createOrderDialogRef"
    :book="book"
    :form="orderForm"
    :service-types="serviceTypes"
    :error-message="createOrderErrorMessage"
    :is-submitting="isCreatingOrder"
    mode="locked"
    :locked-customer="selectedCustomer"
    @close="createOrderErrorMessage = ''"
    @submit="submitCreateOrder"
  />

  <ServiceOrderDetailDialog
    ref="orderDetailDialogRef"
    :book="book"
    :error-message="selectedCustomerOrderErrorMessage"
    :is-loading-order-detail="isLoadingSelectedCustomerOrder"
    :is-updating-order-status="isUpdatingSelectedCustomerOrderStatus"
    :items="selectedCustomerOrderItems"
    :order="selectedCustomerOrder"
    @cancel="handleOrderDetailDialogCancel"
    @change-order-status="updateSelectedCustomerOrderStatus"
    @close="handleOrderDetailDialogClose"
  />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { createServiceCustomer, createServiceOrder, fetchServiceCustomer, fetchServiceCustomers, fetchServiceOrder, fetchServiceTypes, updateServiceCustomer, updateServiceOrderStatus } from '@/api/service'
import { getApiErrorMessage, isNotFoundError } from '@/api/errors'
import { useClearableSearch } from '@/composables/use-clearable-search'
import { useToast } from '@/composables/use-toast'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import CreateServiceOrderDialog from '@/views/book-types/service/dialogs/CreateServiceOrderDialog.vue'
import ServiceClientDialog from '@/views/book-types/service/dialogs/ServiceClientDialog.vue'
import ServiceOrderDetailDialog from '@/views/book-types/service/dialogs/ServiceOrderDetailDialog.vue'
import { createServiceOrderItemRow } from '@/views/book-types/service/dialogs/orderItemRow'
import { getServiceOrderStatusMeta } from '@/views/book-types/service/serviceOrderStatus'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const { t } = useI18n()

const customerDialogRef = ref(null)
const createOrderDialogRef = ref(null)
const orderDetailDialogRef = ref(null)
const customerList = ref([])
const selectedCustomerId = ref('')
const selectedCustomer = ref(null)
const selectedCustomerOrders = ref([])
const activeCustomerOrderId = ref('')
const selectedCustomerOrder = ref(null)
const selectedCustomerOrderItems = ref([])
const serviceTypes = ref([])
const { feedbackMessage, showToast, clearToast } = useToast()

const customerErrorMessage = ref('')
const selectedCustomerErrorMessage = ref('')
const selectedCustomerOrderErrorMessage = ref('')
const customerDialogErrorMessage = ref('')
const createOrderErrorMessage = ref('')
const serviceTypesErrorMessage = ref('')

const isLoadingCustomers = ref(false)
const isLoadingSelectedCustomer = ref(false)
const isLoadingSelectedCustomerOrder = ref(false)
const isSubmittingCustomer = ref(false)
const isCreatingOrder = ref(false)
const isLoadingServiceTypes = ref(false)
const isUpdatingSelectedCustomerOrderStatus = ref(false)

const customerDialogMode = ref('create')
const editingCustomerId = ref('')
const orderForm = ref(createLockedCustomerOrderForm(''))
const {
  cancelPendingSearch: cancelPendingCustomerSearch,
  clearSearch: clearCustomerSearch,
  handleSearchInput: handleCustomerSearchInput,
  hasActiveSearch: hasActiveCustomerSearch,
  searchQuery: customerSearchQuery,
} = useClearableSearch({
  onSearch: (query) => {
    void loadCustomerList(query)
  },
})

const customerForm = reactive({
  name: '',
  phone: '',
  messenger: '',
  address: '',
  location: '',
})

const customerDialogTitle = computed(() => (
  customerDialogMode.value === 'edit'
    ? t('service.clients.editClient')
    : t('service.clients.addClient')
))

const customerDialogSubmitLabel = computed(() => (
  customerDialogMode.value === 'edit'
    ? t('common.actions.saveChanges')
    : t('common.actions.create')
))

const isCustomerSubmitDisabled = computed(() => (
  isSubmittingCustomer.value
  || String(customerForm.name ?? '').trim() === ''
  || String(customerForm.phone ?? '').trim() === ''
))

watch(() => props.book.id, () => {
  cancelPendingCustomerSearch()
  clearSelectedCustomer()
  clearToast()
  customerSearchQuery.value = ''
  serviceTypes.value = []
  serviceTypesErrorMessage.value = ''
  void loadCustomerList('')
}, { immediate: true })

async function loadCustomerList(search = customerSearchQuery.value.trim()) {
  isLoadingCustomers.value = true
  customerErrorMessage.value = ''

  try {
    const response = await fetchServiceCustomers(props.book.id, { search })
    customerList.value = Array.isArray(response.data?.customers) ? response.data.customers : []
    syncSelectedCustomerSummary()
  } catch (error) {
    customerErrorMessage.value = getApiErrorMessage(error, t('service.clientMessages.unableLoad'))
  } finally {
    isLoadingCustomers.value = false
  }
}

function syncSelectedCustomerSummary() {
  if (selectedCustomerId.value === '' || !selectedCustomer.value) {
    return
  }

  const matchingCustomer = customerList.value.find((customer) => customer.id === selectedCustomerId.value) ?? null

  if (matchingCustomer !== null) {
    selectedCustomer.value = {
      ...selectedCustomer.value,
      ...matchingCustomer,
    }
  }
}

async function selectCustomer(customer) {
  selectedCustomerId.value = String(customer.id ?? '')
  selectedCustomer.value = {
    ...customer,
  }
  selectedCustomerOrders.value = []
  await loadCustomerDetails(selectedCustomerId.value)
}

async function loadCustomerDetails(customerId) {
  if (customerId === '') {
    return
  }

  isLoadingSelectedCustomer.value = true
  selectedCustomerErrorMessage.value = ''

  try {
    const response = await fetchServiceCustomer(props.book.id, customerId)
    selectedCustomer.value = response.data?.customer ?? null
    selectedCustomerOrders.value = Array.isArray(response.data?.orders) ? response.data.orders : []
  } catch (error) {
    clearSelectedCustomer()
    selectedCustomerErrorMessage.value = isNotFoundError(error)
      ? t('service.clientMessages.unavailable')
      : getApiErrorMessage(error, t('service.clientMessages.unableLoadDetail'))
  } finally {
    isLoadingSelectedCustomer.value = false
  }
}

function clearSelectedCustomer() {
  selectedCustomerId.value = ''
  selectedCustomer.value = null
  selectedCustomerOrders.value = []
  selectedCustomerErrorMessage.value = ''
  isLoadingSelectedCustomer.value = false
  resetSelectedCustomerOrderDetail()
  closeOrderDetailDialog()
}

function openCreateCustomerDialog() {
  clearToast()
  customerDialogMode.value = 'create'
  editingCustomerId.value = ''
  customerDialogErrorMessage.value = ''
  resetCustomerForm()
  customerDialogRef.value?.open()
}

function openEditCustomerDialog() {
  if (!selectedCustomer.value) {
    return
  }

  clearToast()
  customerDialogMode.value = 'edit'
  editingCustomerId.value = String(selectedCustomer.value.id ?? '')
  customerDialogErrorMessage.value = ''
  customerForm.name = String(selectedCustomer.value.name ?? '')
  customerForm.phone = String(selectedCustomer.value.phone ?? '')
  customerForm.messenger = String(selectedCustomer.value.messenger ?? '')
  customerForm.address = String(selectedCustomer.value.address ?? '')
  customerForm.location = String(selectedCustomer.value.location ?? '')
  customerDialogRef.value?.open()
}

async function submitCustomer() {
  customerDialogErrorMessage.value = ''
  isSubmittingCustomer.value = true

  try {
    const payload = makeCustomerPayload()

    if (customerDialogMode.value === 'edit' && editingCustomerId.value !== '') {
      const response = await updateServiceCustomer(props.book.id, editingCustomerId.value, payload)
      const updatedCustomerId = String(response.data?.customer?.id ?? editingCustomerId.value)

      showToast(t('service.clientMessages.updated'))
      customerDialogRef.value?.close()
      await loadCustomerList()
      selectedCustomerId.value = updatedCustomerId
      await loadCustomerDetails(updatedCustomerId)
    } else {
      const response = await createServiceCustomer(props.book.id, payload)
      const createdCustomerId = String(response.data?.customer?.id ?? '')

      showToast(t('service.clientMessages.created'))
      customerDialogRef.value?.close()
      customerSearchQuery.value = ''
      await loadCustomerList('')

      const createdCustomer = customerList.value.find((entry) => entry.id === createdCustomerId)

      if (createdCustomer) {
        await selectCustomer(createdCustomer)
      } else if (createdCustomerId !== '') {
        selectedCustomerId.value = createdCustomerId
        await loadCustomerDetails(createdCustomerId)
      }
    }
  } catch (error) {
    customerDialogErrorMessage.value = getApiErrorMessage(
      error,
      customerDialogMode.value === 'edit'
        ? t('service.clientMessages.unableUpdate')
        : t('service.clientMessages.unableCreate')
    )
  } finally {
    isSubmittingCustomer.value = false
  }
}

async function ensureServiceTypesLoaded() {
  if (serviceTypes.value.length > 0) {
    return true
  }

  isLoadingServiceTypes.value = true
  serviceTypesErrorMessage.value = ''

  try {
    const response = await fetchServiceTypes(props.book.id)
    serviceTypes.value = Array.isArray(response.data?.serviceTypes) ? response.data.serviceTypes : []
    return true
  } catch (error) {
    serviceTypesErrorMessage.value = getApiErrorMessage(error, t('service.clientMessages.unableLoadServiceTypes'))
    return false
  } finally {
    isLoadingServiceTypes.value = false
  }
}

async function openCreateOrderDialogForCustomer() {
  if (!selectedCustomer.value) {
    return
  }

  clearToast()
  createOrderErrorMessage.value = ''

  const loaded = await ensureServiceTypesLoaded()

  if (!loaded) {
    return
  }

  orderForm.value = createLockedCustomerOrderForm(String(selectedCustomer.value.id ?? ''))
  createOrderDialogRef.value?.open()
}

async function submitCreateOrder() {
  createOrderErrorMessage.value = ''
  isCreatingOrder.value = true

  try {
    const payload = makeLockedCustomerOrderPayload(orderForm.value)
    await createServiceOrder(props.book.id, payload)

    createOrderDialogRef.value?.close()
    showToast(t('service.orderMessages.created'))

    await loadCustomerList()

    if (selectedCustomerId.value !== '') {
      await loadCustomerDetails(selectedCustomerId.value)
    }
  } catch (error) {
    createOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableCreate'))
  } finally {
    isCreatingOrder.value = false
  }
}

async function openOrderDetailDialog(orderId) {
  if (!orderId) {
    return
  }

  activeCustomerOrderId.value = String(orderId)
  selectedCustomerOrder.value = null
  selectedCustomerOrderItems.value = []
  selectedCustomerOrderErrorMessage.value = ''
  isLoadingSelectedCustomerOrder.value = true
  orderDetailDialogRef.value?.open()

  try {
    const response = await fetchServiceOrder(props.book.id, orderId)
    selectedCustomerOrder.value = response.data?.order ?? null
    selectedCustomerOrderItems.value = Array.isArray(response.data?.items) ? response.data.items : []
  } catch (error) {
    selectedCustomerOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableLoadDetail'))
  } finally {
    isLoadingSelectedCustomerOrder.value = false
  }
}

function closeOrderDetailDialog() {
  orderDetailDialogRef.value?.close()
}

function handleOrderDetailDialogCancel(event) {
  if (isLoadingSelectedCustomerOrder.value || isUpdatingSelectedCustomerOrderStatus.value) {
    event.preventDefault()
  }
}

function handleOrderDetailDialogClose() {
  resetSelectedCustomerOrderDetail()
}

async function updateSelectedCustomerOrderStatus(nextStatus) {
  if (!selectedCustomerOrder.value?.id) {
    return
  }

  isUpdatingSelectedCustomerOrderStatus.value = true
  selectedCustomerOrderErrorMessage.value = ''

  try {
    const response = await updateServiceOrderStatus(props.book.id, selectedCustomerOrder.value.id, {
      order_status: nextStatus,
    })
    const updatedOrder = response.data?.order ?? null

    if (!updatedOrder) {
      return
    }

    selectedCustomerOrder.value = updatedOrder
    selectedCustomerOrders.value = selectedCustomerOrders.value.map((order) => (
      order.id === updatedOrder.id
        ? { ...order, ...updatedOrder }
        : order
    ))

    showToast(t('service.orderMessages.statusUpdated'))
  } catch (error) {
    selectedCustomerOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableUpdateStatus'))
  } finally {
    isUpdatingSelectedCustomerOrderStatus.value = false
  }
}

function resetSelectedCustomerOrderDetail() {
  activeCustomerOrderId.value = ''
  selectedCustomerOrder.value = null
  selectedCustomerOrderItems.value = []
  selectedCustomerOrderErrorMessage.value = ''
  isLoadingSelectedCustomerOrder.value = false
  isUpdatingSelectedCustomerOrderStatus.value = false
}

function makeCustomerPayload() {
  return {
    name: String(customerForm.name ?? '').trim(),
    phone: String(customerForm.phone ?? '').trim(),
    messenger: String(customerForm.messenger ?? '').trim(),
    address: String(customerForm.address ?? '').trim(),
    location: String(customerForm.location ?? '').trim(),
  }
}

function makeLockedCustomerOrderPayload(form) {
  return {
    customer_id: String(form.customer_id ?? '').trim(),
    items: form.items.map((item) => ({
      object_name: String(item.object_name ?? '').trim(),
      service_type_id: String(item.service_type_id ?? '').trim(),
      quantity: String(item.quantity ?? '').trim(),
      unit_code: String(item.unit_code ?? '').trim(),
      unit_price: String(item.unit_price ?? '').trim(),
      note: String(item.note ?? '').trim(),
    })),
    discount_amount: String(form.discount_amount ?? '').trim(),
    note: String(form.note ?? '').trim(),
  }
}

function createLockedCustomerOrderForm(customerId) {
  return {
    customer_id: customerId,
    customer: {
      name: '',
      phone: '',
      messenger: '',
      address: '',
      location: '',
    },
    items: [
      createServiceOrderItemRow(),
    ],
    discount_amount: '0.00',
    note: '',
  }
}

function resetCustomerForm() {
  customerForm.name = ''
  customerForm.phone = ''
  customerForm.messenger = ''
  customerForm.address = ''
  customerForm.location = ''
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

function getOrderStatusMeta(status) {
  return getServiceOrderStatusMeta(status)
}

function looksLikeUrl(value) {
  return /^https?:\/\//i.test(String(value ?? '').trim())
}
</script>
