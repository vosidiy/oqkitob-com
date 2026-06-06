<template>
  <dialog ref="dialogRef" class="dialog-xl max-h-full mt-5" @cancel="emit('cancel', $event)" @close="emit('close')">
    <div class="d-flex flex-col" style="max-height:96vh;">
      <header class="dialog-header">
        <h5>{{ $t('service.dialogs.createOrder') }}</h5>
        <button class="btn btn-icon" :disabled="isSubmitting" @click="close">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>

      <div class="dialog-body max-h-initial">
        <form id="form-create-service-order" @submit.prevent="emit('submit')">
          <div v-if="errorMessage" class="alert alert-danger mb-4" role="alert">
            {{ errorMessage }}
          </div>

          <div v-if="serviceTypes.length === 0" class="alert alert-warning mb-4" role="alert">
            {{ $t('service.dialogs.createServiceTypeFirst') }}
          </div>

          <section class="mb-4 card border bg-primary-100 p-4">
            
            <div v-if="isLockedCustomerMode" class="d-flex flex-col gap-2">
              
              <h6 class="mb-1">👤
                  {{ lockedCustomer?.name }}
              </h6>
              <p>
                <span v-if="lockedCustomer?.phone">
                  📞 {{ lockedCustomer.phone }}
                 </span>
                
                 <span v-if="lockedCustomer?.messenger">
                 •  💬 {{ $t('service.fields.messenger') }}: {{ lockedCustomer.messenger }}
                </span>
                <span v-if="lockedCustomer?.address">
                  •   🏠 {{ $t('service.fields.address') }}: {{ lockedCustomer.address }}
                </span>
              </p>
              <p v-if="lockedCustomer?.location">
                📍 {{ $t('service.fields.location') }}: {{ lockedCustomer.location }}
              </p>
            </div>

            <template v-else>
              <h6 class="mb-4">👤 {{ $t('service.dialogs.customerInfo') }}</h6>
              <div class="row gap-3">
                <div class="col-6 mb-3">
                  <div class="d-flex align-items-center">
                    <span class="p-2 flex-shrink-0 bg-white h-9 rounded-left"> 📞 +998 </span>
                    <input
                      id="service-order-customer-phone"
                      v-model.trim="form.customer.phone"
                      type="text"
                      class="form-control h-9 rounded-left-0"
                      placeholder="901234567"
                      :disabled="isSubmitting"
                      required
                    >
                  </div>
                </div>

                <div class="col-6 mb-3">
                  <input
                    id="service-order-customer-name"
                    v-model.trim="form.customer.name"
                    type="text"
                    class="form-control"
                    :placeholder="$t('service.dialogs.customerNamePlaceholder')"
                    :disabled="isSubmitting"
                    required
                  >
                </div>
              </div>

              <div class="row gap-3">
                <div class="col-6 mb-3">
                  <div class="relative">
                    <span class="d-inline-flex absolute ml-1 p-2 left-0 text-lg center-y">💬</span>
                    <input
                      id="service-order-customer-messenger"
                      v-model.trim="form.customer.messenger"
                      type="text"
                      class="form-control pl-9"
                      :placeholder="$t('service.dialogs.messengerPlaceholder')"
                      :disabled="isSubmitting"
                    >
                  </div>
                </div>

                <div class="col-6 mb-3">
                  <div class="relative">
                    <span class="d-inline-flex absolute ml-1 p-2 left-0 text-lg center-y"> 🏠 </span>
                    <input
                      id="service-order-customer-address"
                      v-model.trim="form.customer.address"
                      type="text"
                      class="form-control pl-9"
                      :placeholder="$t('service.dialogs.addressPlaceholder')"
                      :disabled="isSubmitting"
                    >
                  </div>
                </div>
              </div>

              <div class="d-none">
                <input
                  id="service-order-customer-location"
                  v-model.trim="form.customer.location"
                  type="text"
                  class="form-control"
                  :placeholder="$t('service.dialogs.locationPlaceholder')"
                  :disabled="isSubmitting"
                >
              </div>
            </template>
          </section>

          <hr>

          <section class="mb-4">
            <article
              v-for="(item, index) in form.items"
              :key="item._key"
              class="d-flex flex-row border-bottom border-width-2 py-3 border-color-neutral-500"
            >
              <div class="flex-1">
                <div class="row gap-cols-2 mb-3">
                  <div class="col-6">
                    <label class="form-label" :for="`service-order-object-${item._key}`">
                      {{ $t('service.fields.objectName') }}
                    </label>
                    <input
                      :id="`service-order-object-${item._key}`"
                      v-model.trim="item.object_name"
                      type="text"
                      class="form-control"
                      :placeholder="$t('service.dialogs.objectNamePlaceholder')"
                      :disabled="isSubmitting"
                      required
                    >
                  </div>

                  <div class="col-6">
                    <label class="form-label" :for="`service-order-service-${item._key}`">
                      {{ $t('service.fields.serviceType') }}
                    </label>
                    <select
                      :id="`service-order-service-${item._key}`"
                      v-model="item.service_type_id"
                      class="form-select"
                      :disabled="isSubmitting || serviceTypes.length === 0"
                      required
                      @change="applyServiceDefaults(item)"
                    >
                      <option value="">{{ $t('service.dialogs.chooseService') }}</option>
                      <option v-for="serviceType in serviceTypes" :key="serviceType.id" :value="serviceType.id">
                        {{ serviceType.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="row gap-cols-2 mb-3">
                  <div class="col-6">
                    <div class="d-flex gap-2">
                      <input
                        :id="`service-order-quantity-${item._key}`"
                        v-model.trim="item.quantity"
                        type="number"
                        class="form-control"
                        min="0"
                        step="1"
                        :placeholder="$t('service.dialogs.quantityPlaceholder')"
                        :disabled="isSubmitting"
                        required
                      >
                      <select
                        v-model="item.unit_code"
                        class="form-select"
                        :disabled="isSubmitting"
                        required
                      >
                        <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                          {{ $t(unit.labelKey) }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="relative d-flex flex-row">
                      <span class="p-2 flex-shrink-0 bg-neutral-200 rounded-left">{{ $t('service.fields.unitPrice') }} </span>
                      <input
                        :id="`service-order-price-${item._key}`"
                        v-model.trim="item.unit_price"
                        type="number"
                        class="form-control rounded-left-0"
                        min="0"
                        step="1"
                        placeholder="0.00"
                        :disabled="isSubmitting"
                        required
                      >
                      <small class="currency-code text-secondary text-default bg-neutral-50 absolute right-2 top-1 p-1">
                        {{ book.currency_code }}
                      </small>
                    </div>
                  </div>
                </div>

                <div>
                  <input
                    :id="`service-order-note-${item._key}`"
                    v-model.trim="item.note"
                    type="text"
                    class="form-control"
                    :placeholder="$t('service.dialogs.itemNotePlaceholder')"
                    :disabled="isSubmitting"
                  >
                </div>
              </div>

              <div class="p-3 border text-right bg-neutral-200 min-w-40 rounded ml-2">
                <p class="text-secondary">
                  {{ formatQuantityDisplay(item.quantity) }} × {{ formatMoney(lineTotal(item)) }} =
                </p>
                <p class="text-lg mb-3 font-semibold max-w-40" style="word-break: break-all;">
                  {{ formatMoney(lineTotal(item)) }} <small class="currency-code">{{ book.currency_code }}</small>
                </p>

                <button
                  v-if="form.items.length > 1"
                  type="button"
                  class="btn btn-link text-red btn-sm"
                  :disabled="isSubmitting"
                  @click="removeItemRow(index)"
                >
                  {{ $t('common.actions.delete') }}
                </button>
              </div>
            </article>

            <div class="pt-4">
              <button
                type="button"
                class="btn w-full btn-neutral"
                :disabled="isSubmitting"
                @click="addItemRow"
              >
                {{ $t('service.dialogs.addAnotherItem') }}
              </button>
            </div>
          </section>

          <section class="mb-4">
            <label class="form-label" for="service-order-main-note">{{ $t('service.fields.orderNote') }}</label>
            <textarea
              id="service-order-main-note"
              v-model.trim="form.note"
              class="form-control"
              rows="2"
              :placeholder="$t('service.dialogs.orderNotePlaceholder')"
              :disabled="isSubmitting"
            ></textarea>
          </section>

          <hr>

          <section class="d-flex flex-col align-items-end pr-2 mt-4">
            <div class="d-flex mb-1 col-5 align-items-center justify-content-between gap-3">
              <label class="col-6 font-bold" for="service-order-discount">{{ $t('common.fields.discount') }}:</label>
              <div class="col-6 text-right font-semibold">
                <div class="relative">
                  <input
                    id="service-order-discount"
                    v-model.trim="form.discount_amount"
                    type="number"
                    class="form-control min-h-5"
                    min="0"
                    step="1"
                    :placeholder="$t('service.dialogs.moneyPlaceholder')"
                    :disabled="isSubmitting"
                  >
                  <small class="currency-code text-secondary text-default bg-neutral-50 absolute right-2 top-1 p-1">
                    {{ book.currency_code }}
                  </small>
                </div>
              </div>
            </div>

            <div class="d-flex col-5 align-items-center justify-content-between gap-3">
              <label class="col-6 font-bold" for="service-order-paid">{{ $t('common.fields.paid') }}:</label>
              <div class="col-6 text-right font-semibold">
                <div class="relative">
                  <input
                    id="service-order-paid"
                    v-model.trim="form.paid_amount"
                    type="number"
                    class="form-control min-h-5"
                    min="0"
                    step="1"
                    :placeholder="$t('service.dialogs.moneyPlaceholder')"
                    :disabled="isSubmitting"
                  >
                  <small class="currency-code text-secondary text-default bg-neutral-50 absolute right-2 top-1 p-1">
                    {{ book.currency_code }}
                  </small>
                </div>
              </div>
            </div>
          </section>

          <hr>

          <section class="d-flex flex-col align-items-end pr-2">
            <div class="d-flex col-5 justify-content-between gap-3">
              <span>{{ $t('common.fields.subtotal') }}</span>
              <strong>{{ formatMoney(subtotalAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
            </div>
            <div class="d-flex col-5 justify-content-between gap-3">
              <span>{{ $t('common.fields.discount') }}</span>
              <strong>- {{ formatMoney(discountAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
            </div>
            <div class="d-flex col-5 justify-content-between gap-3">
              <span>{{ $t('service.orders.totalToPay') }}</span>
              <strong>{{ formatMoney(totalAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
            </div>
            <div class="d-flex col-5 justify-content-between gap-3">
              <span class="text-green">{{ $t('common.fields.paid') }}</span>
              <strong class="text-green">{{ formatMoney(paidAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
            </div>
            <div class="d-flex col-5 justify-content-between gap-3">
              <span class="text-orange">{{ $t('service.orders.remainingDue') }}</span>
              <strong class="text-orange">{{ formatMoney(dueAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
            </div>
          </section>
        </form>
      </div>

      <footer class="dialog-bottom">
        <button
          type="submit"
          class="btn btn-primary"
          form="form-create-service-order"
          :disabled="isSubmitDisabled"
        >
          <span v-if="isSubmitting">{{ $t('common.states.saving') }}</span>
          <span v-else>{{ $t('service.dialogs.createOrderSubmit') }}</span>
        </button>
        <button type="button" class="btn btn-default" :disabled="isSubmitting" @click="close">
          {{ $t('common.actions.cancel') }}
        </button>

        <p class="ml-auto d-flex gap-3 text-xl">
          <span>{{ $t('service.orders.totalToPay') }}</span>
          <strong>{{ formatMoney(totalAmount) }} <small class="currency-code">{{ book.currency_code }}</small></strong>
        </p>
      </footer>
    </div>
  </dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay } from '@/utils/quantity'
import { createServiceOrderItemRow, SERVICE_UNIT_OPTIONS } from './orderItemRow'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  lockedCustomer: {
    type: Object,
    default: null,
  },
  mode: {
    type: String,
    default: 'editable',
  },
  serviceTypes: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['cancel', 'close', 'submit'])
const dialogRef = ref(null)
const unitOptions = SERVICE_UNIT_OPTIONS

const isLockedCustomerMode = computed(() => props.mode === 'locked' && props.lockedCustomer !== null)
const subtotalAmount = computed(() => props.form.items.reduce((sum, item) => sum + lineTotal(item), 0))
const discountAmount = computed(() => clampMoney(props.form.discount_amount, subtotalAmount.value))
const totalAmount = computed(() => Math.max(0, subtotalAmount.value - discountAmount.value))
const paidAmount = computed(() => clampMoney(props.form.paid_amount, totalAmount.value))
const dueAmount = computed(() => Math.max(0, totalAmount.value - paidAmount.value))
const isSubmitDisabled = computed(() => props.isSubmitting || props.serviceTypes.length === 0)

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

function addItemRow() {
  props.form.items.push(createServiceOrderItemRow())
}

function removeItemRow(index) {
  if (props.form.items.length <= 1) {
    return
  }

  props.form.items.splice(index, 1)
}

function applyServiceDefaults(item) {
  const serviceType = props.serviceTypes.find((entry) => entry.id === item.service_type_id)

  if (!serviceType) {
    return
  }

  item.unit_code = String(serviceType.default_unit ?? 'qty')
  item.unit_price = String(serviceType.default_price ?? '0')
}

function lineTotal(item) {
  return roundCurrency(parseQuantity(item.quantity) * parseMoney(item.unit_price))
}

function parseMoney(value) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) ? parsedValue : 0
}

function parseQuantity(value) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) ? parsedValue : 0
}

function clampMoney(value, max) {
  const normalizedValue = roundCurrency(Math.max(0, parseMoney(value)))

  return roundCurrency(Math.min(normalizedValue, Math.max(0, max)))
}

function roundCurrency(value) {
  return Math.round(value * 100) / 100
}

function formatMoney(value) {
  return formatMoneyByBookSettings(value, props.book)
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
