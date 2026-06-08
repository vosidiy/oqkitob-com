<template>
  <main class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="flex-1">
          <input
            v-model="receivedDateFilter"
            type="date"
            class="form-control"
            @change="handleReceivedDateChange"
          >
        </div>
        <div class="flex-1">
          <div class="relative">
            <input
              v-model.trim="reportSearchQuery"
              type="search"
              :placeholder="$t('service.reports.searchPlaceholder')"
              class="form-control"
              @input="handleReportSearchInput"
            >
            <button
              v-if="hasActiveReportSearch"
              type="button"
              class="btn btn-neutral top-0 right-0 absolute"
              :title="$t('common.actions.clearSearch')"
              @click="clearReportSearch"
            >
              ✕
            </button>
          </div>
        </div>
      </header>

      <div v-if="feedbackMessage" class="toast" role="status">
        ✅ {{ feedbackMessage }}
      </div>

      <div v-if="reportOrdersErrorMessage" class="alert alert-danger m-4" role="alert">
        {{ reportOrdersErrorMessage }}
      </div>

      <section class="flex-1 overflow-y-auto">
        <div v-if="isLoadingReportOrders" class="px-4 py-6">
          <div class="skeleton">
            <b>{{ $t('service.reports.loadingOrders') }}</b>
          </div>
        </div>

        <div v-else-if="reportOrders.length === 0" class="p-4 text-secondary">
          <p class="p-3 text-lg text-center">
            {{ hasActiveReportFilters ? $t('service.reports.noFilteredOrders') : $t('service.reports.noDeliveredOrders') }}
          </p>
        </div>

        <ul v-else class="mt-1 mb-10">
          <li v-for="order in reportOrders" :key="order.id" class="border-bottom hover:bg-neutral-100">
            <a
              href="#"
              role="button"
              class="px-4 p-3 text-base d-block"
              :class="{ 'bg-primary-200': selectedOrderId === order.id }"
              @click.prevent="selectOrder(order.id)"
            >
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"></path><path d="M14 8H8"></path><path d="M16 12H8"></path><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"></path></svg>
                </div>
                <div>
                  <h6 class="mb-1">
                    {{ $t('service.orders.itemsCount', { count: Number(order.item_count ?? 0) }) }}
                    |
                    {{ $t('common.fields.total') }}:
                    <span>{{ formatMoney(order.total_amount) }} <small class="currency-code">{{ order.currency_code }}</small></span>
                  </h6>
                  <p>
                    {{ order.customer_name || $t('service.orders.walkInCustomer') }}
                    <span v-if="order.customer_phone"> • 📞 {{ order.customer_phone }}</span>
                  </p>
                  <p class="text-secondary">
                    {{ $t('service.orders.receivedAt') }}: {{ formatDateTime(order.received_at) }}
                  </p>
                </div>
                <div class="ml-auto text-right">
                  <p>{{ $t('common.fields.status') }}:</p>
                  <p :class="getOrderStatusMeta(order.order_status).textClass">
                    {{ getOrderStatusMeta(order.order_status).emoji }}
                    {{ $t(getOrderStatusMeta(order.order_status).labelKey) }}
                  </p>
                </div>
              </div>
            </a>
          </li>
        </ul>

        <nav
          v-if="reportOrders.length > 0 && reportPagination.total_pages > 1"
          class="d-flex flex-wrap gap-1 px-4 mb-10"
        >
          <button
            type="button"
            class="btn btn-neutral"
            :disabled="isLoadingReportOrders || reportCurrentPage <= 1"
            @click="changeReportPage(reportCurrentPage - 1)"
          >
            {{ $t('service.reports.previousPage') }}
          </button>
          <button
            v-for="page in reportPageNumbers"
            :key="page"
            type="button"
            class="btn min-w-10"
            :class="page === reportCurrentPage ? 'btn-primary' : 'btn-neutral'"
            :disabled="isLoadingReportOrders || page === reportCurrentPage"
            @click="changeReportPage(page)"
          >
            {{ page }}
          </button>
          <button
            type="button"
            class="btn btn-neutral"
            :disabled="isLoadingReportOrders || reportCurrentPage >= reportPagination.total_pages"
            @click="changeReportPage(reportCurrentPage + 1)"
          >
            {{ $t('service.reports.nextPage') }}
          </button>
        </nav>
      </section>
    </aside>

    <aside class="col-6 mobile:max-w-full flex-grow mobile:w-full">
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
              <div class="d-flex align-items-start gap-2">
                <ServiceOrderStatusDropdown
                  :order-status="selectedOrder.order_status"
                  :is-updating="isUpdatingSelectedOrderStatus"
                  @change-status="updateSelectedOrderStatus"
                />
                <button type="button" class="btn btn-neutral" :disabled="isUpdatingSelectedOrderStatus" @click="clearSelectedOrder">
                  {{ $t('common.actions.close') }}
                </button>
              </div>
            </header>

            <div class="mb-4">
              <p>
                <strong>{{ $t('common.fields.customer') }}:</strong>
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
                <strong>{{ $t('service.orders.receivedAt') }}:</strong>
                {{ formatDateTime(selectedOrder.received_at) }}
              </p>
              <p v-if="selectedOrder.delivered_at">
                <strong>{{ $t('service.orders.deliveredAt') }}:</strong>
                {{ formatDateTime(selectedOrder.delivered_at) }}
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
                    <td>{{ formatQuantity(item.quantity) }} {{ $t('service.units.' + item.unit_code) }}</td>
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
                <span>{{ $t('common.fields.total') }}</span>
                <strong>{{ formatMoney(selectedOrder.total_amount) }} <small class="currency-code">{{ selectedOrder.currency_code }}</small></strong>
              </div>
            </div>
          </div>
        </article>

        <article v-else>
          <p class="text-lg p-10 text-secondary text-center">
            👈 {{ $t('service.reports.selectOrderHint') }}
          </p>

          <div v-if="reportAnalyticsErrorMessage" class="alert alert-danger mb-4" role="alert">
            {{ reportAnalyticsErrorMessage }}
          </div>

          <div class="card card-body mb-4">
            <header class="mb-4">
              <select
                v-model="selectedReportFilterTime"
                name="service-report-filter-time"
                class="form-select"
              >
                <option value="today">{{ $t('service.reports.filters.today') }}</option>
                <option value="yesterday">{{ $t('service.reports.filters.yesterday') }}</option>
                <option value="last_10_days">{{ $t('service.reports.filters.last10Days') }}</option>
                <option value="last_30_days">{{ $t('service.reports.filters.last30Days') }}</option>
              </select>
            </header>

            <h6 class="text-lg mb-4">
              {{ $t('service.reports.summaryTitle') }}
              ({{ $t(reportFilterTitleKeys[selectedReportFilterTime] || reportFilterTitleKeys.today) }})
            </h6>

            <div v-if="isLoadingReportAnalytics" class="text-secondary">
              {{ $t('service.reports.loadingAnalytics') }}
            </div>

            <ul v-else class="d-grid gap-2 grid-template-cols-2 mobile:grid-template-cols-1 lh-sm">
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('service.reports.summaryCount') }}:</p>
                <strong class="text-xl">{{ reportAnalyticsSummary.order_count }}</strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('service.reports.summaryDiscountAmount') }}:</p>
                <strong class="text-xl">- {{ formatMoney(reportAnalyticsSummary.total_discount_amount) }} <small class="currency-code">{{ props.book.currency_code }}</small></strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">{{ $t('service.reports.summaryTotalAmount') }}:</p>
                <strong class="text-xl">{{ formatMoney(reportAnalyticsSummary.total_amount) }} <small class="currency-code">{{ props.book.currency_code }}</small></strong>
              </li>
            </ul>

            <hr>

            <h5 class="mb-3">
              {{ $t('service.reports.servicesTitle') }}
              ({{ $t(reportFilterTitleKeys[selectedReportFilterTime] || reportFilterTitleKeys.today) }})
            </h5>

            <div v-if="isLoadingReportAnalytics" class="text-secondary">
              {{ $t('service.reports.loadingAnalytics') }}
            </div>
            <p v-else-if="reportAnalyticsServices.length === 0" class="text-secondary">
              {{ $t('service.reports.noServices') }}
            </p>
            <div v-else class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>{{ $t('service.fields.serviceType') }}</th>
                    <th class="text-right">{{ $t('common.fields.quantity') }}</th>
                    <th class="text-right">{{ $t('common.fields.total') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="service in reportAnalyticsServices"
                    :key="`${service.service_name}-${service.unit_code}`"
                  >
                    <td>{{ service.service_name }}</td>
                    <td class="text-right">
                      {{ formatQuantity(service.total_quantity) }} {{ $t('service.units.' + service.unit_code) }}
                    </td>
                    <td class="text-right">
                      {{ formatMoney(service.total_amount) }} <small class="currency-code">{{ props.book.currency_code }}</small>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </article>
      </section>
    </aside>
  </main>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { fetchServiceOrder, fetchServiceOrderAnalytics, fetchServiceOrders, updateServiceOrderStatus } from '@/api/service'
import { getApiErrorMessage, isNotFoundError } from '@/api/errors'
import { useClearableSearch } from '@/composables/use-clearable-search'
import { useToast } from '@/composables/use-toast'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay } from '@/utils/quantity'
import ServiceOrderStatusDropdown from '@/views/book-types/service/components/ServiceOrderStatusDropdown.vue'
import { getServiceOrderStatusMeta } from '@/views/book-types/service/serviceOrderStatus'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const { t } = useI18n()
const reportOrders = ref([])
const reportAnalyticsSummary = ref(makeEmptyReportAnalyticsSummary())
const reportAnalyticsServices = ref([])
const selectedOrderId = ref('')
const selectedOrder = ref(null)
const selectedOrderItems = ref([])
const receivedDateFilter = ref('')
const selectedReportFilterTime = ref('today')
const reportCurrentPage = ref(1)
const reportPerPage = 20
const reportPagination = ref(makeEmptyReportPagination())
const { feedbackMessage, showToast, clearToast } = useToast()
const {
  cancelPendingSearch: cancelPendingReportSearch,
  clearSearch: baseClearReportSearch,
  handleSearchInput: handleReportSearchInput,
  hasActiveSearch: hasActiveReportSearch,
  searchQuery: reportSearchQuery,
} = useClearableSearch({
  onSearch: (query) => {
    reportCurrentPage.value = 1
    void loadReportOrders(query, receivedDateFilter.value, 1)
  },
})

const isLoadingReportOrders = ref(false)
const isLoadingReportAnalytics = ref(false)
const isLoadingSelectedOrder = ref(false)
const isUpdatingSelectedOrderStatus = ref(false)

const reportOrdersErrorMessage = ref('')
const reportAnalyticsErrorMessage = ref('')
const selectedOrderErrorMessage = ref('')

let latestReportOrdersRequestId = 0
let latestReportAnalyticsRequestId = 0
let latestSelectedOrderRequestId = 0

const reportFilterTitleKeys = {
  last_10_days: 'service.reports.filters.last10Days',
  last_30_days: 'service.reports.filters.last30Days',
  today: 'service.reports.filters.today',
  yesterday: 'service.reports.filters.yesterday',
}

const hasActiveReportFilters = computed(() => {
  return hasActiveReportSearch.value || String(receivedDateFilter.value ?? '').trim() !== ''
})
const reportPageNumbers = computed(() => {
  return Array.from({ length: reportPagination.value.total_pages }, (_, index) => index + 1)
})

watch(() => props.book.id, async () => {
  cancelPendingReportSearch()
  clearToast()
  reportSearchQuery.value = ''
  receivedDateFilter.value = ''
  reportCurrentPage.value = 1
  selectedReportFilterTime.value = 'today'
  clearSelectedOrder()

  const localNow = makeLocalDateTimeString()
  await Promise.all([
    loadReportOrders('', '', 1),
    loadReportAnalytics(localNow),
  ])
}, { immediate: true })

watch(selectedReportFilterTime, async () => {
  await loadReportAnalytics(makeLocalDateTimeString())
})

async function loadReportOrders(
  search = reportSearchQuery.value,
  receivedOn = receivedDateFilter.value,
  page = reportCurrentPage.value
) {
  const requestId = ++latestReportOrdersRequestId

  isLoadingReportOrders.value = true
  reportOrdersErrorMessage.value = ''
  clearSelectedOrder()

  try {
    const { data } = await fetchServiceOrders(props.book.id, {
      order_status: 'delivered',
      search,
      received_on: String(receivedOn ?? '').trim(),
      page,
      per_page: reportPerPage,
    })

    if (!isLatestReportOrdersRequest(requestId)) {
      return
    }

    reportOrders.value = Array.isArray(data?.orders) ? data.orders : []
    reportPagination.value = normalizeReportPagination(data?.pagination, page, reportPerPage)
    reportCurrentPage.value = reportPagination.value.page
  } catch (error) {
    if (!isLatestReportOrdersRequest(requestId)) {
      return
    }

    reportOrders.value = []
    reportPagination.value = makeEmptyReportPagination()
    reportCurrentPage.value = 1
    reportOrdersErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableLoad'))
  } finally {
    if (isLatestReportOrdersRequest(requestId)) {
      isLoadingReportOrders.value = false
    }
  }
}

async function loadReportAnalytics(localNow = makeLocalDateTimeString()) {
  const requestId = ++latestReportAnalyticsRequestId

  isLoadingReportAnalytics.value = true
  reportAnalyticsErrorMessage.value = ''

  try {
    const { data } = await fetchServiceOrderAnalytics(props.book.id, {
      filter_time: selectedReportFilterTime.value,
      local_now: localNow,
    })

    if (!isLatestReportAnalyticsRequest(requestId)) {
      return
    }

    reportAnalyticsSummary.value = normalizeReportAnalyticsSummary(data?.summary)
    reportAnalyticsServices.value = Array.isArray(data?.services) ? data.services : []
  } catch (error) {
    if (!isLatestReportAnalyticsRequest(requestId)) {
      return
    }

    reportAnalyticsSummary.value = makeEmptyReportAnalyticsSummary()
    reportAnalyticsServices.value = []
    reportAnalyticsErrorMessage.value = getApiErrorMessage(error, t('service.reports.unableLoadAnalytics'))
  } finally {
    if (isLatestReportAnalyticsRequest(requestId)) {
      isLoadingReportAnalytics.value = false
    }
  }
}

function handleReceivedDateChange() {
  cancelPendingReportSearch()
  reportCurrentPage.value = 1
  void loadReportOrders(reportSearchQuery.value, receivedDateFilter.value, 1)
}

function clearReportSearch() {
  reportCurrentPage.value = 1
  baseClearReportSearch()
}

function changeReportPage(page) {
  if (
    isLoadingReportOrders.value
    || page < 1
    || page > reportPagination.value.total_pages
    || page === reportCurrentPage.value
  ) {
    return
  }

  reportCurrentPage.value = page
  void loadReportOrders(reportSearchQuery.value, receivedDateFilter.value, page)
}

async function selectOrder(orderId) {
  if (!orderId) {
    return
  }

  const requestId = ++latestSelectedOrderRequestId

  selectedOrderId.value = orderId
  selectedOrder.value = null
  selectedOrderItems.value = []
  selectedOrderErrorMessage.value = ''
  isLoadingSelectedOrder.value = true

  try {
    const { data } = await fetchServiceOrder(props.book.id, orderId)

    if (!isLatestSelectedOrderRequest(requestId)) {
      return
    }

    selectedOrder.value = data?.order ?? null
    selectedOrderItems.value = Array.isArray(data?.items) ? data.items : []
  } catch (error) {
    if (!isLatestSelectedOrderRequest(requestId)) {
      return
    }

    clearSelectedOrder()

    if (isNotFoundError(error)) {
      removeReportOrderFromList(orderId)
      selectedOrderErrorMessage.value = t('service.reports.orderUnavailable')
      return
    }

    selectedOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableLoadDetail'))
  } finally {
    if (isLatestSelectedOrderRequest(requestId)) {
      isLoadingSelectedOrder.value = false
    }
  }
}

function clearSelectedOrder() {
  latestSelectedOrderRequestId += 1
  selectedOrderId.value = ''
  selectedOrder.value = null
  selectedOrderItems.value = []
  selectedOrderErrorMessage.value = ''
  isLoadingSelectedOrder.value = false
}

async function updateSelectedOrderStatus(nextStatus) {
  if (!selectedOrder.value?.id) {
    return
  }

  isUpdatingSelectedOrderStatus.value = true
  selectedOrderErrorMessage.value = ''

  try {
    const response = await updateServiceOrderStatus(props.book.id, selectedOrder.value.id, {
      order_status: nextStatus,
    })
    const updatedOrder = response.data?.order ?? null

    showToast(t('service.orderMessages.statusUpdated'))

    if (!updatedOrder) {
      await Promise.all([
        loadReportOrders(reportSearchQuery.value, receivedDateFilter.value, reportCurrentPage.value),
        loadReportAnalytics(makeLocalDateTimeString()),
      ])
      return
    }

    if (updatedOrder.order_status !== 'delivered') {
      clearSelectedOrder()
      await Promise.all([
        loadReportOrders(reportSearchQuery.value, receivedDateFilter.value, reportCurrentPage.value),
        loadReportAnalytics(makeLocalDateTimeString()),
      ])
      return
    }

    selectedOrder.value = updatedOrder
    reportOrders.value = reportOrders.value.map((order) => (
      order.id === updatedOrder.id
        ? { ...order, ...updatedOrder }
        : order
    ))
  } catch (error) {
    selectedOrderErrorMessage.value = getApiErrorMessage(error, t('service.orderMessages.unableUpdateStatus'))
  } finally {
    isUpdatingSelectedOrderStatus.value = false
  }
}

function removeReportOrderFromList(orderId) {
  reportOrders.value = reportOrders.value.filter((order) => order.id !== orderId)
}

function isLatestReportOrdersRequest(requestId) {
  return requestId === latestReportOrdersRequestId
}

function isLatestReportAnalyticsRequest(requestId) {
  return requestId === latestReportAnalyticsRequestId
}

function isLatestSelectedOrderRequest(requestId) {
  return requestId === latestSelectedOrderRequestId
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

function formatQuantity(value) {
  return formatQuantityDisplay(value)
}

function getOrderStatusMeta(status) {
  return getServiceOrderStatusMeta(status)
}

function looksLikeUrl(value) {
  return /^https?:\/\//i.test(String(value ?? '').trim())
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

function makeEmptyReportAnalyticsSummary() {
  return {
    order_count: 0,
    total_discount_amount: '0.00',
    total_amount: '0.00',
  }
}

function normalizeReportAnalyticsSummary(summary) {
  return {
    order_count: Number(summary?.order_count) || 0,
    total_discount_amount: String(summary?.total_discount_amount ?? '0.00'),
    total_amount: String(summary?.total_amount ?? '0.00'),
  }
}

function makeEmptyReportPagination() {
  return {
    page: 1,
    per_page: reportPerPage,
    total_items: 0,
    total_pages: 1,
  }
}

function normalizeReportPagination(pagination, fallbackPage = 1, fallbackPerPage = reportPerPage) {
  const page = Math.max(1, Number(pagination?.page) || fallbackPage)
  const perPage = Math.max(1, Number(pagination?.per_page) || fallbackPerPage)
  const totalItems = Math.max(0, Number(pagination?.total_items) || 0)
  const totalPages = Math.max(1, Number(pagination?.total_pages) || Math.ceil(totalItems / perPage) || 1)

  return {
    page,
    per_page: perPage,
    total_items: totalItems,
    total_pages: totalPages,
  }
}
</script>
