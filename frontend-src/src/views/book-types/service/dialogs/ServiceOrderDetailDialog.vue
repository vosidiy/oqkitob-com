<template>
  <dialog ref="dialogRef" class="dialog-xl mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <div class="mr-auto">
        <h5>{{ $t('service.orders.detailTitle') }}</h5>
        <p v-if="order" class="text-secondary text-sm">{{ order.id }}</p>
      </div>
      <div class="d-flex align-items-start gap-2">
        <ServiceOrderStatusDropdown
          v-if="order"
          :order-status="order.order_status"
          :is-updating="isUpdatingOrderStatus"
          @change-status="emit('change-order-status', $event)"
        />
        <button class="btn btn-icon" :disabled="isLoadingOrderDetail || isUpdatingOrderStatus" @click="close">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </div>
    </header>

    <div class="dialog-body">
      <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
        {{ errorMessage }}
      </div>

      <div v-if="isLoadingOrderDetail" class="text-secondary">
        {{ $t('service.orders.loadingOrderDetail') }}
      </div>

      <div v-else-if="order">
        <article class="mb-4">
          <p>
            <strong>{{ $t('common.fields.customer') }}:</strong>
            👤 {{ order.customer_name || '-' }}
            <span v-if="order.customer_phone"> · 📞 {{ order.customer_phone }}</span>
          </p>
          <p v-if="order.customer_messenger">
            <strong>{{ $t('service.fields.messenger') }}:</strong>
            {{ order.customer_messenger }}
          </p>
          <p v-if="order.customer_address">
            <strong>{{ $t('service.fields.address') }}:</strong>
            {{ order.customer_address }}
          </p>
          <p v-if="order.customer_location">
            <strong>{{ $t('service.fields.location') }}:</strong>
            <a
              v-if="looksLikeUrl(order.customer_location)"
              :href="order.customer_location"
              target="_blank"
              rel="noreferrer"
              class="link"
            >
              {{ order.customer_location }}
            </a>
            <span v-else>{{ order.customer_location }}</span>
          </p>
          <hr>
          <p>
            <strong>{{ $t('service.orders.receivedAt') }}:</strong>
            {{ formatDateTime(order.received_at) }}
          </p>
          <p v-if="order.note">
            <strong>{{ $t('common.fields.note') }}:</strong>
            {{ order.note }}
          </p>
        </article>

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
              <tr v-for="item in items" :key="item.id">
                <td>
                  <div>{{ item.object_name }}</div>
                  <small v-if="item.note">{{ item.note }}</small>
                </td>
                <td>{{ item.service_name }}</td>
                <td>{{ formatQuantity(item.quantity) }} {{ $t('service.units.' + item.unit_code) }}</td>
                <td class="text-right">
                  {{ formatMoney(item.unit_price) }} <small class="currency-code">{{ order.currency_code }}</small>
                </td>
                <td class="text-right">
                  {{ formatMoney(item.line_total) }} <small class="currency-code">{{ order.currency_code }}</small>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex flex-col align-items-end pr-2 mt-4">
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('common.fields.subtotal') }}</span>
            <strong>{{ formatMoney(order.subtotal_amount) }} <small class="currency-code">{{ order.currency_code }}</small></strong>
          </div>
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('common.fields.discount') }}</span>
            <strong>- {{ formatMoney(order.discount_amount) }} <small class="currency-code">{{ order.currency_code }}</small></strong>
          </div>
          <div class="d-flex col-6 justify-content-between gap-3">
            <span>{{ $t('common.fields.total') }}</span>
            <strong>{{ formatMoney(order.total_amount) }} <small class="currency-code">{{ order.currency_code }}</small></strong>
          </div>
        </div>
      </div>

      <footer class="pt-3 border-top mt-4">
        <div class="float-right">
          <button type="button" class="btn btn-default" :disabled="isLoadingOrderDetail || isUpdatingOrderStatus" @click="close">
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
import ServiceOrderStatusDropdown from '@/views/book-types/service/components/ServiceOrderStatusDropdown.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  isLoadingOrderDetail: {
    type: Boolean,
    default: false,
  },
  isUpdatingOrderStatus: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array,
    default: () => [],
  },
  order: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['cancel', 'change-order-status', 'close'])
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

function looksLikeUrl(value) {
  return /^https?:\/\//i.test(String(value ?? '').trim())
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
