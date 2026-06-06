<template>
  <main class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="max-w-40%  d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <button class="btn btn-primary w-full" @click="openCreateOrderDialog">
          {{ $t('service.orders.createOrder') }}
          </button>
        </div>
        <div class="flex-grow">
          <div class="relative">
            <input type="search" placeholder="Chek ID, Mijoz nomi" class="form-control">
            <button class="btn btn-neutral top-0 right-0 absolute" title="Clear search"> ✕ </button>
          </div>
        </div>
      </header>

      <div v-if="feedbackMessage" class="toast" role="status">
        ✅ {{ feedbackMessage }}
      </div>

      <div v-if="ordersListErrorMessage" class="alert alert-danger m-4" role="alert">
        {{ ordersListErrorMessage }}
      </div>

      <section class="flex-1 overflow-y-auto">
        <div v-if="isLoadingOrdersList" class="px-4 py-6">
          <div class="skeleton">
            <b>{{ $t('service.orders.loadingOrders') }}</b>
          </div>
        </div>

        <div v-else-if="orders.length === 0" class="p-4 text-secondary">
          <p class="p-3 text-lg text-center">
            {{ $t('service.orders.empty') }}
          </p>
        </div>

        <ul v-else class="mt-1 mb-10">
          <li v-for="order in orders" :key="order.id" class="border-bottom hover:bg-neutral-100">
            <a
              href="#"
              role="button"
              class="px-4 py-4 text-base d-block"
              :class="{ 'bg-primary-200': selectedOrderId === order.id }"
              @click.prevent="selectOrder(order.id)"
            >
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                </div>
                <div>
                  <h6 class="mb-1 text-lg">
                    👤 {{ order.customer_name || $t('service.orders.walkInCustomer') }}
                    <span v-if="order.customer_phone" class="text-secondary"> • 📞 {{ order.customer_phone }}</span>
                  </h6>
                  <p class="mb-0">
                    {{ $t('service.orders.itemsCount', { count: Number(order.item_count ?? 0) }) }}
                    |
                    <span>{{ formatMoney(order.total_amount) }} <small class="currency-code">{{ order.currency_code }}</small></span>
                  </p>
                  <p class="text-secondary">
                    {{ $t('service.orders.receivedAt') }}: {{ formatDateTime(order.received_at) }}
                  </p>
                </div>

                <div class="ml-auto text-right">
                  <p class="badge" :class="orderStatusBadgeClass(order.order_status)">
                    {{ $t('service.orderStatusLabels.' + order.order_status) }}
                  </p>
                  <p class="text-secondary text-sm mt-2">
                    {{ $t('common.fields.status') }}:
                    {{ $t('service.paymentStatusLabels.' + order.payment_status) }}
                  </p>
                </div>
              </div>
            </a>
          </li>
        </ul>
      </section>
    </aside>

    <aside class="mobile:max-w-full flex-grow mobile:w-full">
      <section class="flex-1 overflow-y-auto p-3 bg-lower h-full">
        
        <div v-if="selectedOrderErrorMessage" class="alert alert-danger mb-4" role="alert">
          {{ selectedOrderErrorMessage }}
        </div>

        <article v-if="isLoadingSelectedOrder" class="card">
          <div class="p-10 text-center text-secondary text-lg">
            {{ $t('service.orders.loadingOrderDetail') }}
          </div>
        </article>

        <article v-else-if="selectedOrder" class="card">
          
          <div class="card-body">
            <header class="d-flex mb-2">
              <div class="flex-grow">
                <h3 class="text-2xl">{{ $t('service.orders.detailTitle') }}</h3>
                <p class="text-secondary text-xs">ID: {{ selectedOrder.id }}</p>
              </div>
              <div>
                <button type="button" class="btn btn-neutral" @click="clearSelectedOrder">
                  {{ $t('common.actions.close') }}
                </button>
              </div>
            </header>

            <div class="mb-4">
              <p>
                <strong> {{ $t('common.fields.customer') }}:</strong>
                👤 {{ selectedOrder.customer_name || '-' }}
                <span v-if="selectedOrder.customer_phone"> · 📞 {{ selectedOrder.customer_phone }}</span>
              </p>
              <p v-if="selectedOrder.customer_messenger">
                <strong>{{ $t('service.fields.messenger') }}:</strong>
                {{ selectedOrder.customer_messenger }}
              </p>
              <p v-if="selectedOrder.customer_address">
                <strong>{{ $t('service.fields.address') }}:</strong>
                {{ selectedOrder.customer_address }}
              </p>
              <p v-if="selectedOrder.customer_location">
                <strong>{{ $t('service.fields.location') }}:</strong>
                <a
                  v-if="looksLikeUrl(selectedOrder.customer_location)"
                  :href="selectedOrder.customer_location"
                  target="_blank"
                  rel="noreferrer"
                  class="link"
                >
                  {{ selectedOrder.customer_location }}
                </a>
                <span v-else>{{ selectedOrder.customer_location }}</span>
              </p>
              <hr>
              <p>
                <strong>{{ $t('common.fields.status') }}:</strong>
                {{ $t('service.orderStatusLabels.' + selectedOrder.order_status) }}
              </p>
              <p>
                <strong>{{ $t('service.orders.receivedAt') }}:</strong>
                {{ formatDateTime(selectedOrder.received_at) }}
              </p>
              <p>
                <strong>{{ $t('service.orders.paymentStatus') }}:</strong>
                {{ $t('service.paymentStatusLabels.' + selectedOrder.payment_status) }}
              </p>
              <p v-if="selectedOrder.note">
                <strong>{{ $t('common.fields.note') }}:</strong>
                {{ selectedOrder.note }}
              </p>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th width="30%">{{ $t('service.fields.objectName') }}</th>
                    <th width="20%">{{ $t('service.fields.serviceType') }}</th>
                    <th>{{ $t('common.fields.quantity') }}</th>
                    <th width="20%" class="text-right">{{ $t('service.fields.unitPrice') }}</th>
                    <th width="20%" class="text-right">=</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrderItems" :key="item.id">
                    <td>
                      <div>{{ item.object_name }}</div>
                      <small v-if="item.note">{{ item.note }}</small>
                    </td>
                    <td>{{ item.service_name }}</td>
                    <td>{{ formatQuantityDisplay(item.quantity) }} {{ $t('service.units.' + item.unit_code) }}</td>
                    <td class="text-right">
                      {{ formatMoney(item.unit_price) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small>
                    </td>
                    <td class="text-right">
                      {{ formatMoney(item.line_total) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="d-flex flex-col align-items-end pr-2">
              <div class="d-flex col-6 justify-content-between gap-3">
                <span>{{ $t('common.fields.subtotal') }}</span>
                <strong>{{ formatMoney(selectedOrder.subtotal_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
              <div class="d-flex col-6 justify-content-between gap-3">
                <span>{{ $t('common.fields.discount') }}</span>
                <strong>- {{ formatMoney(selectedOrder.discount_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
              <div class="d-flex col-6 justify-content-between gap-3">
                <span>{{ $t('service.orders.totalToPay') }}</span>
                <strong>{{ formatMoney(selectedOrder.total_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
              <div class="d-flex col-6 justify-content-between gap-3">
                <span class="text-green">{{ $t('common.fields.paid') }}</span>
                <strong class="text-green">{{ formatMoney(selectedOrder.paid_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
              <div class="d-flex col-6 justify-content-between gap-3">
                <span class="text-orange">{{ $t('service.orders.remainingDue') }}</span>
                <strong class="text-orange">{{ formatMoney(selectedOrder.due_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
            </div>
          </div>
        </article>

        <article v-else>

          <p class="text-lg p-10 mt-5 text-secondary text-center">
             👈 {{ $t('service.orders.selectOrderPlaceholder') }}
          </p>

          <div class="card card-body border-primary border-width-2 m-5">
           
            <h6 class="text-lg mb-4">{{ $t('service.serviceTypes.title') }}</h6>

            <div v-if="serviceTypesErrorMessage" class="alert alert-danger mb-4" role="alert">
              {{ serviceTypesErrorMessage }}
            </div>

            <div v-if="isLoadingServiceTypes" class="skeleton">
              <b>{{ $t('service.serviceTypes.loading') }}</b>
            </div>

            <div v-else-if="serviceTypes.length === 0" class="text-secondary">
              {{ $t('service.serviceTypes.empty') }}
            </div>


            <table class="table" id="book_service_type">
              <thead>
                <tr>
                  <th>{{ $t('common.fields.name') }}</th>
                  <th>{{ $t('service.fields.defaultPrice') }}</th>
                  <th>{{ $t('service.fields.defaultUnit') }}</th>
                  <th class="text-right">{{ $t('common.actions.settings') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="serviceType in serviceTypes" :key="serviceType.id">
                  <td> <strong>{{ serviceType.name }}</strong></td>
                  <td>{{ formatMoney(serviceType.default_price) }} <small class="currency-code">{{ book.currency_code }}</small></td>
                  <td>{{ $t('service.units.' + serviceType.default_unit) }}</td>
                  <td class="text-right">
                    <button type="button" class="btn btn-link btn-sm" @click="openEditServiceTypeDialog(serviceType)">
                      {{ $t('common.actions.edit') }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-link btn-sm text-red"
                      :disabled="isDeletingServiceTypeId === serviceType.id"
                      @click="removeServiceType(serviceType)"
                    >
                      <span v-if="isDeletingServiceTypeId === serviceType.id">{{ $t('common.states.deleting') }}</span>
                      <span v-else>{{ $t('common.actions.delete') }}</span>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type="button" class="btn btn-sm btn-neutral" @click="openCreateServiceTypeDialog">
                      {{ $t('service.serviceTypes.addAction') }}
                  </button>
                  </td>
                  <td colspan="3"> </td>
                </tr>
              </tbody>
            </table>

          </div>
          
          

        </article>
       
        
      </section>
    </aside>
  </main>

  <CreateServiceOrderDialog
    ref="createOrderDialogRef"
    :book="book"
    :form="orderForm"
    :service-types="serviceTypes"
    :error-message="createOrderErrorMessage"
    :is-submitting="isCreatingOrder"
    @close="createOrderErrorMessage = ''"
    @submit="submitCreateOrder"
  />

  <ServiceTypeDialog
    ref="serviceTypeDialogRef"
    :book="book"
    :form="serviceTypeForm"
    :title="serviceTypeDialogTitle"
    :submit-label="serviceTypeSubmitLabel"
    :error-message="serviceTypeErrorMessage"
    :is-submitting="isSubmittingServiceType"
    :is-submit-disabled="isSubmittingServiceType"
    @close="serviceTypeErrorMessage = ''"
    @submit="submitServiceType"
  />
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  createServiceOrder,
  createServiceType,
  deleteServiceType,
  fetchServiceOrder,
  fetchServiceOrders,
  fetchServiceTypes,
  updateServiceType,
} from '@/api/service'
import { getApiErrorMessage } from '@/api/errors'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay } from '@/utils/quantity'
import CreateServiceOrderDialog from '@/views/book-types/service/dialogs/CreateServiceOrderDialog.vue'
import ServiceTypeDialog from '@/views/book-types/service/dialogs/ServiceTypeDialog.vue'
import { createServiceOrderItemRow } from '@/views/book-types/service/dialogs/orderItemRow'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})
const { t } = useI18n()

const orders = ref([])
const selectedOrderId = ref('')
const selectedOrder = ref(null)
const selectedOrderItems = ref([])
const serviceTypes = ref([])
const feedbackMessage = ref('')

const isLoadingOrdersList = ref(false)
const isLoadingSelectedOrder = ref(false)
const isLoadingServiceTypes = ref(false)
const isCreatingOrder = ref(false)
const isSubmittingServiceType = ref(false)
const isDeletingServiceTypeId = ref('')

const ordersListErrorMessage = ref('')
const selectedOrderErrorMessage = ref('')
const serviceTypesErrorMessage = ref('')
const createOrderErrorMessage = ref('')
const serviceTypeErrorMessage = ref('')

const createOrderDialogRef = ref(null)
const serviceTypeDialogRef = ref(null)

const serviceTypeDialogMode = ref('create')
const editingServiceTypeId = ref('')

const orderForm = ref(createEmptyOrderForm())
const serviceTypeForm = ref(createEmptyServiceTypeForm())

const serviceTypeDialogTitle = computed(() => (
  serviceTypeDialogMode.value === 'edit'
    ? t('service.dialogs.editServiceType')
    : t('service.dialogs.createServiceType')
))
const serviceTypeSubmitLabel = computed(() => (
  serviceTypeDialogMode.value === 'edit'
    ? t('common.actions.update')
    : t('common.actions.create')
))

onMounted(() => {
  void loadInitialData()
})

async function loadInitialData() {
  clearFeedbackMessage()

  await Promise.all([
    loadOrdersList(),
    loadServiceTypes(),
  ])
}

async function loadOrdersList() {
  isLoadingOrdersList.value = true
  ordersListErrorMessage.value = ''

  try {
    const response = await fetchServiceOrders(props.book.id)
    orders.value = Array.isArray(response.data?.orders) ? response.data.orders : []
  } catch (error) {
    ordersListErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableLoad'))
  } finally {
    isLoadingOrdersList.value = false
  }
}

async function loadOrderDetail(orderId) {
  if (!orderId) {
    return
  }

  selectedOrderId.value = orderId
  selectedOrder.value = null
  selectedOrderItems.value = []
  isLoadingSelectedOrder.value = true
  selectedOrderErrorMessage.value = ''

  try {
    const response = await fetchServiceOrder(props.book.id, orderId)
    selectedOrder.value = response.data?.order ?? null
    selectedOrderItems.value = Array.isArray(response.data?.items) ? response.data.items : []
  } catch (error) {
    selectedOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableLoadDetail'))
  } finally {
    isLoadingSelectedOrder.value = false
  }
}

async function loadServiceTypes() {
  isLoadingServiceTypes.value = true
  serviceTypesErrorMessage.value = ''

  try {
    const response = await fetchServiceTypes(props.book.id)
    serviceTypes.value = Array.isArray(response.data?.serviceTypes) ? response.data.serviceTypes : []
  } catch (error) {
    serviceTypesErrorMessage.value = getApiErrorMessage(error, t('service.serviceTypeMessages.unableLoad'))
  } finally {
    isLoadingServiceTypes.value = false
  }
}

async function selectOrder(orderId) {
  clearFeedbackMessage()
  await loadOrderDetail(orderId)
}

function clearSelectedOrder() {
  selectedOrderId.value = ''
  selectedOrder.value = null
  selectedOrderItems.value = []
  selectedOrderErrorMessage.value = ''
}

function openCreateOrderDialog() {
  clearFeedbackMessage()
  createOrderErrorMessage.value = ''
  orderForm.value = createEmptyOrderForm()
  createOrderDialogRef.value?.open()
}

async function submitCreateOrder() {
  createOrderErrorMessage.value = ''
  isCreatingOrder.value = true

  try {
    const payload = makeCreateOrderPayload(orderForm.value)
    const response = await createServiceOrder(props.book.id, payload)
    const createdOrderId = String(response.data?.order?.id ?? '').trim()

    createOrderDialogRef.value?.close()
    feedbackMessage.value = t('service.orderMessages.created')

    await loadOrdersList()

    if (createdOrderId !== '') {
      await loadOrderDetail(createdOrderId)
    }
  } catch (error) {
    createOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableCreate'))
  } finally {
    isCreatingOrder.value = false
  }
}

function openCreateServiceTypeDialog() {
  clearFeedbackMessage()
  serviceTypeDialogMode.value = 'create'
  editingServiceTypeId.value = ''
  serviceTypeErrorMessage.value = ''
  serviceTypeForm.value = createEmptyServiceTypeForm()
  serviceTypeDialogRef.value?.open()
}

function openEditServiceTypeDialog(serviceType) {
  clearFeedbackMessage()
  serviceTypeDialogMode.value = 'edit'
  editingServiceTypeId.value = String(serviceType?.id ?? '')
  serviceTypeErrorMessage.value = ''
  serviceTypeForm.value = {
    name: String(serviceType?.name ?? ''),
    default_unit: String(serviceType?.default_unit ?? 'qty'),
    default_price: String(serviceType?.default_price ?? '0.00'),
  }
  serviceTypeDialogRef.value?.open()
}

async function submitServiceType() {
  serviceTypeErrorMessage.value = ''
  isSubmittingServiceType.value = true

  try {
    const payload = {
      name: String(serviceTypeForm.value.name ?? '').trim(),
      default_unit: String(serviceTypeForm.value.default_unit ?? '').trim(),
      default_price: String(serviceTypeForm.value.default_price ?? '').trim(),
    }

    if (serviceTypeDialogMode.value === 'edit' && editingServiceTypeId.value !== '') {
      await updateServiceType(props.book.id, editingServiceTypeId.value, payload)
      feedbackMessage.value = t('service.serviceTypeMessages.updated')
    } else {
      await createServiceType(props.book.id, payload)
      feedbackMessage.value = t('service.serviceTypeMessages.created')
    }

    serviceTypeDialogRef.value?.close()
    await loadServiceTypes()
  } catch (error) {
    serviceTypeErrorMessage.value = getApiErrorMessage(error, t('service.serviceTypeMessages.unableSave'))
  } finally {
    isSubmittingServiceType.value = false
  }
}

async function removeServiceType(serviceType) {
  const confirmed = window.confirm(t('service.serviceTypeMessages.confirmDelete'))

  if (!confirmed) {
    return
  }

  clearFeedbackMessage()
  serviceTypesErrorMessage.value = ''
  isDeletingServiceTypeId.value = String(serviceType?.id ?? '')

  try {
    await deleteServiceType(props.book.id, isDeletingServiceTypeId.value)
    feedbackMessage.value = t('service.serviceTypeMessages.deleted')
    await loadServiceTypes()
  } catch (error) {
    serviceTypesErrorMessage.value = getApiErrorMessage(error, t('service.serviceTypeMessages.unableDelete'))
  } finally {
    isDeletingServiceTypeId.value = ''
  }
}

function makeCreateOrderPayload(form) {
  return {
    customer: {
      name: String(form.customer.name ?? '').trim(),
      phone: String(form.customer.phone ?? '').trim(),
      messenger: String(form.customer.messenger ?? '').trim(),
      address: String(form.customer.address ?? '').trim(),
      location: String(form.customer.location ?? '').trim(),
    },
    items: form.items.map((item) => ({
      object_name: String(item.object_name ?? '').trim(),
      service_type_id: String(item.service_type_id ?? '').trim(),
      quantity: String(item.quantity ?? '').trim(),
      unit_code: String(item.unit_code ?? '').trim(),
      unit_price: String(item.unit_price ?? '').trim(),
      note: String(item.note ?? '').trim(),
    })),
    discount_amount: String(form.discount_amount ?? '').trim(),
    paid_amount: String(form.paid_amount ?? '').trim(),
    note: String(form.note ?? '').trim(),
  }
}

function createEmptyOrderForm() {
  return {
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
    paid_amount: '0.00',
    note: '',
  }
}

function createEmptyServiceTypeForm() {
  return {
    name: '',
    default_unit: 'qty',
    default_price: '0.00',
  }
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

function orderStatusBadgeClass(status) {
  if (status === 'ready' || status === 'delivered') {
    return 'badge-green'
  }

  if (status === 'working') {
    return 'badge-orange'
  }

  return 'badge-orange'
}

function looksLikeUrl(value) {
  return /^https?:\/\//i.test(String(value ?? '').trim())
}

function clearFeedbackMessage() {
  feedbackMessage.value = ''
}
</script>
