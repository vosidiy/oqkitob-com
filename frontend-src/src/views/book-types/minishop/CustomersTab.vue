<template>
  <section class="d-flex flex-1 overflow-hidden mobile:flex-col">
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
        <div v-if="customerErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ customerErrorMessage }}
        </div>

        <div v-else-if="isLoadingCustomers" class="px-4 py-4 text-secondary">
          {{ $t('minishop.customers.loadingCustomers') }}
        </div>

        <div v-else-if="customerList.length === 0" class="px-4 py-4 text-secondary">
          {{ $t('minishop.customers.noCustomers') }}
        </div>

        <ul v-else class="mt-1">
          <li
            v-for="customer in customerList"
            :key="customer.id"
            class="border-bottom hover:bg-neutral-100"
          >
            <div
              role="button"
              class="px-4 p-3"
              :class="{ 'bg-primary-200': selectedCustomerId === customer.id }"
              @click="selectCustomer(customer)"
            >
              <div class="d-flex justify-content-between gap-3">
                <div class="overflow-hidden">
                  <h6 class="mb-1">{{ customer.name }}</h6>
                  <p v-if="customer.phone" class="text-sm text-secondary mb-1">
                    {{ customer.phone }}
                  </p>
                  <p v-if="customer.note" class="text-sm text-secondary mb-0 text-truncate">
                    {{ customer.note }}
                  </p>
                </div>

                <div class="text-right flex-shrink-0">
                  <p
                    class="mb-1"
                    :class="{ 'text-red': Number(customer.outstanding_balance) > 0 }"
                  >
                    {{ formatMoney(customer.outstanding_balance) }}
                  </p>
                  <p class="text-sm text-secondary mb-0">
                    {{ $t('minishop.customers.receiptCount', { count: formatInteger(customer.receipt_count) }) }}
                  </p>
                  <p v-if="customer.last_sale_at" class="text-xs text-secondary mb-0 mt-1">
                    {{ formatDateTime(customer.last_sale_at) }}
                  </p>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </aside>

    <aside class="col-6">
      <section class="flex-1 overflow-y-auto p-4 bg-lower h-full">
        <div v-if="selectedCustomerErrorMessage" class="alert alert-danger mb-4" role="alert">
          {{ selectedCustomerErrorMessage }}
        </div>

        <div v-if="isLoadingSelectedCustomer" class="card">
          <div class="p-10 text-secondary">{{ $t('minishop.customers.loadingCustomer') }}</div>
        </div>

        <div v-else-if="selectedCustomer" class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4 mobile:flex-col">
              <div>
                <h3 class="text-2xl mb-1">{{ selectedCustomer.name }}</h3>
                <p class="text-secondary mb-0">{{ selectedCustomer.id }}</p>
              </div>

              <div class="d-flex gap-2 mobile:w-full">
                <button type="button" class="btn btn-default" @click="clearSelectedCustomer">
                  {{ $t('common.actions.close') }}
                </button>
                <button type="button" class="btn btn-primary" @click="openEditCustomerDialog">
                  {{ $t('minishop.customers.editCustomer') }}
                </button>
              </div>
            </div>

            <div class="row gap-3 mb-4">
              <div class="col border rounded p-3 bg-lower">
                <p class="text-secondary mb-1">{{ $t('minishop.customers.outstandingDebt') }}</p>
                <strong
                  class="text-lg"
                  :class="{ 'text-red': Number(selectedCustomer.outstanding_balance) > 0 }"
                >
                  {{ formatMoney(selectedCustomer.outstanding_balance) }}
                </strong>
              </div>
              <div class="col border rounded p-3 bg-lower">
                <p class="text-secondary mb-1">{{ $t('minishop.customers.receipts') }}</p>
                <strong class="text-lg">{{ formatInteger(selectedCustomer.receipt_count) }}</strong>
              </div>
              <div class="col border rounded p-3 bg-lower">
                <p class="text-secondary mb-1">{{ $t('minishop.customers.totalSales') }}</p>
                <strong class="text-lg">{{ formatMoney(selectedCustomer.total_sales_amount) }}</strong>
              </div>
              <div class="col border rounded p-3 bg-lower">
                <p class="text-secondary mb-1">{{ $t('minishop.customers.totalPaid') }}</p>
                <strong class="text-lg">{{ formatMoney(selectedCustomer.total_paid_amount) }}</strong>
              </div>
            </div>

            <div class="d-flex flex-col gap-2 mb-4">
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.phone') }}</span>
                <strong class="text-right">{{ selectedCustomer.phone || '-' }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('minishop.customers.lastReceipt') }}</span>
                <strong class="text-right">{{ formatDateTime(selectedCustomer.last_sale_at) }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.created') }}</span>
                <strong class="text-right">{{ formatDateTime(selectedCustomer.created_at) }}</strong>
              </div>
              <div class="d-flex justify-content-between gap-3">
                <span>{{ $t('common.fields.updated') }}</span>
                <strong class="text-right">{{ formatDateTime(selectedCustomer.updated_at) }}</strong>
              </div>
            </div>

            <div v-if="selectedCustomer.note" class="mb-4">
              <h4 class="h6 mb-2">{{ $t('common.fields.note') }}</h4>
              <p class="mb-0">{{ selectedCustomer.note }}</p>
            </div>

            <div>
              <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <h4 class="h6 mb-0">{{ $t('minishop.customers.relatedReceipts') }}</h4>
                <span class="text-secondary text-sm">
                  {{ $t('minishop.customers.itemCount', { count: selectedCustomerReceipts.length }) }}
                </span>
              </div>

              <div v-if="selectedCustomerReceipts.length === 0" class="text-secondary">
                {{ $t('minishop.customers.noReceipts') }}
              </div>

              <div v-else class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead>
                    <tr>
                      <th>{{ $t('common.fields.receipt') }}</th>
                      <th>{{ $t('common.fields.soldAt') }}</th>
                      <th>{{ $t('common.fields.total') }}</th>
                      <th>{{ $t('minishop.sales.due') }}</th>
                      <th>{{ $t('common.fields.status') }}</th>
                      <th>{{ $t('common.actions.viewDetail') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="receipt in selectedCustomerReceipts" :key="receipt.id">
                      <td>
                        <div>{{ receipt.id }}</div>
                        <small v-if="receipt.note" class="text-secondary">{{ receipt.note }}</small>
                      </td>
                      <td>{{ formatDateTime(receipt.sold_at) }}</td>
                      <td>{{ formatMoney(receipt.total_amount) }}</td>
                      <td>{{ formatMoney(receipt.due_amount) }}</td>
                      <td class="text-capitalize">{{ translatePaymentStatus($t, receipt.payment_status) }}</td>
                      <td>
                        <div class="d-flex gap-2 flex-wrap">
                          <button
                            type="button"
                            class="btn btn-default"
                            :disabled="isReceiptActionBusy(receipt.id)"
                            @click="openReceiptDetailDialog(receipt.id)"
                          >
                            {{ $t('common.actions.viewDetail') }}
                          </button>
                          <button
                            type="button"
                            class="btn btn-outline text-red"
                            :disabled="isReceiptActionBusy(receipt.id)"
                            @click="handleDeleteReceipt(receipt.id)"
                          >
                            <span v-if="deletingReceiptId === receipt.id">{{ $t('common.states.deleting') }}</span>
                            <span v-else>{{ $t('minishop.customers.deleteSale') }}</span>
                          </button>
                          <button
                            type="button"
                            class="btn btn-primary"
                            :disabled="isReceiptActionBusy(receipt.id)"
                            @click="openReceiptPaymentDialog(receipt.id)"
                          >
                            {{ $t('minishop.customers.changePayment') }}
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="card">
          <div class="card-body text-center py-6">
            <h3 class="h6 mb-2">{{ $t('minishop.customers.selectCustomer') }}</h3>
            <p class="text-secondary mb-0">
              {{ $t('minishop.customers.selectCustomerHint') }}
            </p>
          </div>
        </div>
      </section>
    </aside>

    <dialog
      ref="createCustomerDialog"
      @cancel="handleCreateCustomerDialogCancel"
      @close="handleCreateCustomerDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.customers.createCustomer') }}</h5>
        <button class="btn btn-icon" :disabled="isCreatingCustomer" @click="closeCreateCustomerDialog">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <form @submit.prevent="handleCreateCustomer">
          <div v-if="createCustomerErrorMessage" class="alert alert-danger" role="alert">
            {{ createCustomerErrorMessage }}
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-create-name">{{ $t('common.fields.name') }}</label>
            <input
              id="customers-tab-create-name"
              v-model.trim="createCustomerForm.name"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.enterCustomerName')"
              :disabled="isCreatingCustomer"
              required
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-create-phone">{{ $t('common.fields.phone') }}</label>
            <input
              id="customers-tab-create-phone"
              v-model.trim="createCustomerForm.phone"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.optionalPhone')"
              :disabled="isCreatingCustomer"
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-create-note">{{ $t('common.fields.note') }}</label>
            <textarea
              id="customers-tab-create-note"
              v-model.trim="createCustomerForm.note"
              class="form-control"
              rows="4"
              :placeholder="$t('minishop.customers.optionalNote')"
              :disabled="isCreatingCustomer"
            ></textarea>
          </div>

          <div class="pt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary" :disabled="isCreateCustomerSubmitDisabled">
              <span v-if="isCreatingCustomer">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.create') }}</span>
            </button>
            <button
              type="button"
              class="btn btn-default"
              :disabled="isCreatingCustomer"
              @click="closeCreateCustomerDialog"
            >
              {{ $t('common.actions.cancel') }}
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <dialog
      ref="editCustomerDialog"
      @cancel="handleEditCustomerDialogCancel"
      @close="handleEditCustomerDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.customers.editCustomerTitle') }}</h5>
        <button class="btn btn-icon" :disabled="isUpdatingCustomer" @click="closeEditCustomerDialog">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <form @submit.prevent="handleUpdateCustomer">
          <div v-if="editCustomerErrorMessage" class="alert alert-danger" role="alert">
            {{ editCustomerErrorMessage }}
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-edit-name">{{ $t('common.fields.name') }}</label>
            <input
              id="customers-tab-edit-name"
              v-model.trim="editCustomerForm.name"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.enterCustomerName')"
              :disabled="isUpdatingCustomer"
              required
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-edit-phone">{{ $t('common.fields.phone') }}</label>
            <input
              id="customers-tab-edit-phone"
              v-model.trim="editCustomerForm.phone"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.optionalPhone')"
              :disabled="isUpdatingCustomer"
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="customers-tab-edit-note">{{ $t('common.fields.note') }}</label>
            <textarea
              id="customers-tab-edit-note"
              v-model.trim="editCustomerForm.note"
              class="form-control"
              rows="4"
              :placeholder="$t('minishop.customers.optionalNote')"
              :disabled="isUpdatingCustomer"
            ></textarea>
          </div>

          <div class="pt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary" :disabled="isEditCustomerSubmitDisabled">
              <span v-if="isUpdatingCustomer">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.saveChanges') }}</span>
            </button>
            <button
              type="button"
              class="btn btn-default"
              :disabled="isUpdatingCustomer"
              @click="closeEditCustomerDialog"
            >
              {{ $t('common.actions.cancel') }}
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <dialog
      ref="receiptDetailDialog"
      class="dialog-md"
      @cancel="handleReceiptDetailDialogCancel"
      @close="handleReceiptDetailDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('common.actions.viewDetail') }}</h5>
        <button class="btn btn-icon" :disabled="isLoadingReceiptDetail" @click="closeReceiptDetailDialog">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <div v-if="receiptDetailErrorMessage" class="alert alert-danger mb-4" role="alert">
          {{ receiptDetailErrorMessage }}
        </div>

        <div v-if="isLoadingReceiptDetail" class="text-secondary">
          {{ $t('minishop.sales.loadingReceipt') }}
        </div>

        <div v-else-if="selectedReceiptSale">
          <div class="d-flex justify-content-between align-items-start gap-3 mb-4 mobile:flex-col">
            <div>
              <h3 class="text-2xl mb-1">{{ $t('minishop.sales.receipt') }}</h3>
              <p class="text-secondary mb-0">{{ selectedReceiptSale.id }}</p>
            </div>

            <div class="text-right mobile:text-left">
              <p class="mb-1"><strong>{{ $t('common.fields.soldAt') }}:</strong> {{ formatDateTime(selectedReceiptSale.sold_at) }}</p>
              <p class="mb-1"><strong>{{ $t('common.fields.currency') }}:</strong> {{ selectedReceiptSale.currency_code }}</p>
              <p class="mb-1">
                <strong>{{ $t('common.fields.customer') }}:</strong>
                {{ selectedReceiptSale.customer_name || $t('minishop.sales.noCustomer') }}
                <span v-if="selectedReceiptSale.customer_phone"> · {{ selectedReceiptSale.customer_phone }}</span>
              </p>
              <p class="mb-0 text-capitalize"><strong>{{ $t('common.fields.status') }}:</strong> {{ translatePaymentStatus($t, selectedReceiptSale.payment_status) }}</p>
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
                <tr v-for="item in selectedReceiptItems" :key="item.id">
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
              v-if="selectedReceiptSale.customer_name"
              class="d-flex justify-content-between gap-3"
            >
              <span>{{ $t('common.fields.customer') }}</span>
              <strong class="text-right">
                {{ selectedReceiptSale.customer_name }}
                <span v-if="selectedReceiptSale.customer_phone"> · {{ selectedReceiptSale.customer_phone }}</span>
              </strong>
            </div>
            <div
              v-if="selectedReceiptSale.note"
              class="d-flex justify-content-between gap-3"
            >
              <span>{{ $t('common.fields.note') }}</span>
              <strong class="text-right">{{ selectedReceiptSale.note }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>{{ $t('common.fields.subtotal') }}</span>
              <strong>{{ formatMoney(selectedReceiptSale.subtotal_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>{{ $t('common.fields.discount') }}</span>
              <strong>- {{ formatMoney(selectedReceiptSale.discount_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>{{ $t('common.fields.total') }}</span>
              <strong>{{ formatMoney(selectedReceiptSale.total_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>{{ $t('common.fields.paid') }}</span>
              <strong>{{ formatMoney(selectedReceiptSale.paid_amount) }}</strong>
            </div>
            <div
              v-if="Number(selectedReceiptSale.due_amount) > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>{{ $t('minishop.sales.remainingDebt') }}</span>
              <strong>{{ formatMoney(selectedReceiptSale.due_amount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>{{ $t('common.fields.status') }}</span>
              <strong>{{ $t('common.states.paidInFull') }}</strong>
            </div>
          </div>
        </div>
      </div>
    </dialog>

    <dialog
      ref="receiptPaymentDialog"
      class="dialog-sm"
      @cancel="handleReceiptPaymentDialogCancel"
      @close="handleReceiptPaymentDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.sales.changePaymentTitle') }}</h5>
        <button class="btn btn-icon" :disabled="isUpdatingReceiptPayment" @click="closeReceiptPaymentDialog">
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <form @submit.prevent="handleUpdateReceiptPayment">
          <div v-if="receiptPaymentErrorMessage" class="alert alert-danger mb-3" role="alert">
            {{ receiptPaymentErrorMessage }}
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span class="col-6">{{ $t('common.fields.subtotal') }}</span>
            <div class="col-6 text-right font-semibold">
              {{ formatMoney(paymentSummarySubtotal) }}
            </div>
          </div>

          <div class="row justify-content-between mb-3">
            <label class="col-6 form-label" for="customer-receipt-payment-discount">{{ $t('common.fields.discount') }}</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="customer-receipt-payment-discount"
                v-model.trim="receiptPaymentForm.discountInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isUpdatingReceiptPayment"
                @blur="normalizeReceiptPaymentDiscountInput"
              >
            </div>
          </div>

          <div class="row justify-content-between mb-2">
            <span class="col-6">{{ $t('common.fields.total') }}</span>
            <div class="col-5 text-right font-semibold">
              {{ formatMoney(paymentSummaryTotal) }}
            </div>
          </div>

          <hr>

          <div class="row justify-content-between">
            <label class="col-6 form-label" for="customer-receipt-payment-paid">{{ $t('common.fields.paid') }}</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="customer-receipt-payment-paid"
                v-model.trim="receiptPaymentForm.paidInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isUpdatingReceiptPayment"
                @blur="normalizeReceiptPaymentPaidInput"
              >
            </div>
          </div>
          <hr>

          <div class="mb-5">
            <div
              v-if="paymentSummaryChangeAmount > 0"
              class="d-flex justify-content-between gap-3 text-green"
            >
              <span>{{ $t('minishop.sales.returnChange') }}</span>
              <strong>{{ formatMoney(paymentSummaryChangeAmount) }}</strong>
            </div>
            <div
              v-else-if="paymentSummaryRemainingAmount > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>{{ $t('minishop.sales.remainingDebt') }}</span>
              <strong>{{ formatMoney(paymentSummaryRemainingAmount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>{{ $t('common.fields.status') }}</span>
              <strong>{{ $t('common.states.paidInFull') }}</strong>
            </div>
          </div>

          <div class="border-top d-flex pt-4 gap-2">
            <button
              type="button"
              class="btn btn-default flex-1"
              :disabled="isUpdatingReceiptPayment"
              @click="closeReceiptPaymentDialog"
            >
              {{ $t('common.actions.cancel') }}
            </button>
            <button
              type="submit"
              class="btn btn-primary flex-1"
              :disabled="isUpdatingReceiptPayment || !selectedReceiptSale"
            >
              <span v-if="isUpdatingReceiptPayment">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.save') }}</span>
            </button>
          </div>
        </form>
      </div>
    </dialog>
  </section>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage, isNotFoundError, isUnauthorizedError } from '@/api/errors'
import { translatePaymentStatus } from '@/i18n/helpers'
import {
  createMinishopCustomer,
  deleteMinishopSale,
  fetchMinishopCustomer,
  fetchMinishopCustomers,
  fetchMinishopSale,
  updateMinishopSalePaymentSummary,
  updateMinishopCustomer,
} from '@/api/minishop'

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
const receiptPaymentDialog = ref(null)
const customerList = ref([])
const customerErrorMessage = ref('')
const customerSearchQuery = ref('')
const selectedCustomerId = ref('')
const selectedCustomer = ref(null)
const selectedCustomerReceipts = ref([])
const selectedReceiptSale = ref(null)
const selectedReceiptItems = ref([])
const isLoadingCustomers = ref(false)
const isLoadingSelectedCustomer = ref(false)
const isLoadingReceiptDetail = ref(false)
const isCreatingCustomer = ref(false)
const isUpdatingCustomer = ref(false)
const isUpdatingReceiptPayment = ref(false)
const selectedCustomerErrorMessage = ref('')
const receiptDetailErrorMessage = ref('')
const receiptPaymentErrorMessage = ref('')
const createCustomerErrorMessage = ref('')
const editCustomerErrorMessage = ref('')
const editingCustomerId = ref('')
const activeReceiptId = ref('')
const deletingReceiptId = ref('')
const pendingFocusedCustomerId = ref('')
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

const receiptPaymentForm = reactive({
  discountInput: '0.00',
  paidInput: '0.00',
})

const isCreateCustomerSubmitDisabled = computed(() => {
  return isCreatingCustomer.value || createCustomerForm.name === ''
})

const isEditCustomerSubmitDisabled = computed(() => {
  return isUpdatingCustomer.value || editingCustomerId.value === '' || editCustomerForm.name === ''
})

const paymentSummarySubtotal = computed(() => {
  return parseNonNegativeAmount(selectedReceiptSale.value?.subtotal_amount ?? 0, 0)
})

const paymentSummaryDiscountAmount = computed(() => {
  return Math.min(parseNonNegativeAmount(receiptPaymentForm.discountInput, 0), paymentSummarySubtotal.value)
})

const paymentSummaryTotal = computed(() => {
  return Math.max(paymentSummarySubtotal.value - paymentSummaryDiscountAmount.value, 0)
})

const paymentSummaryPaidAmount = computed(() => {
  return parseNonNegativeAmount(receiptPaymentForm.paidInput, 0)
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

watch(() => props.book.id, () => {
  clearSelectedCustomer()
  customerSearchQuery.value = ''
  void loadCustomerList('')
}, { immediate: true })

async function loadCustomerList(search = customerSearchQuery.value.trim()) {
  isLoadingCustomers.value = true
  customerErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCustomers(props.book.id, { search })
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
  }, 250)
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
  closeReceiptPaymentDialog()
  resetSelectedReceiptState()
}

function openCreateCustomerDialog() {
  createCustomerErrorMessage.value = ''

  if (!createCustomerDialog.value?.open) {
    createCustomerDialog.value?.showModal()
  }
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

  if (!editCustomerDialog.value?.open) {
    editCustomerDialog.value?.showModal()
  }
}

function closeCreateCustomerDialog() {
  if (createCustomerDialog.value?.open) {
    createCustomerDialog.value.close()
  }
}

function closeEditCustomerDialog() {
  if (editCustomerDialog.value?.open) {
    editCustomerDialog.value.close()
  }
}

function openReceiptDetailDialog(receiptId) {
  void loadReceiptSale(receiptId, 'detail')
}

function closeReceiptDetailDialog() {
  if (receiptDetailDialog.value?.open) {
    receiptDetailDialog.value.close()
  }
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

function openReceiptPaymentDialog(receiptId) {
  void loadReceiptSale(receiptId, 'payment')
}

function closeReceiptPaymentDialog() {
  if (receiptPaymentDialog.value?.open) {
    receiptPaymentDialog.value.close()
  }
}

function handleReceiptPaymentDialogCancel(event) {
  if (isUpdatingReceiptPayment.value) {
    event.preventDefault()
  }
}

function handleReceiptPaymentDialogClose() {
  receiptPaymentErrorMessage.value = ''
  receiptPaymentForm.discountInput = '0.00'
  receiptPaymentForm.paidInput = '0.00'
  isUpdatingReceiptPayment.value = false
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

async function loadReceiptSale(receiptId, mode = 'detail') {
  activeReceiptId.value = receiptId
  receiptDetailErrorMessage.value = ''
  receiptPaymentErrorMessage.value = ''
  isLoadingReceiptDetail.value = true

  try {
    const { data } = await fetchMinishopSale(props.book.id, receiptId)
    selectedReceiptSale.value = data.sale ?? null
    selectedReceiptItems.value = Array.isArray(data.items) ? data.items : []

    if (!selectedReceiptSale.value) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    if (mode === 'payment') {
      receiptPaymentForm.discountInput = formatMoney(selectedReceiptSale.value.discount_amount)
      receiptPaymentForm.paidInput = formatMoney(selectedReceiptSale.value.paid_amount)

      if (!receiptPaymentDialog.value?.open) {
        receiptPaymentDialog.value?.showModal()
      }
    } else if (!receiptDetailDialog.value?.open) {
      receiptDetailDialog.value?.showModal()
    }
  } catch (error) {
    const didHandle = await handleReceiptActionError(error, receiptId, t('minishop.customers.unableLoadReceipt'))

    if (!didHandle) {
      const message = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
        ? error.message
        : getApiErrorMessage(error, t('minishop.customers.unableLoadReceipt'))

      if (mode === 'payment') {
        receiptPaymentErrorMessage.value = message
      } else {
        receiptDetailErrorMessage.value = message
      }
      selectedCustomerErrorMessage.value = message
    }
  } finally {
    isLoadingReceiptDetail.value = false
  }
}

async function handleDeleteReceipt(receiptId) {
  if (deletingReceiptId.value !== '' || isUpdatingReceiptPayment.value) {
    return
  }

  if (!window.confirm(t('minishop.customers.confirmDeleteSale'))) {
    return
  }

  selectedCustomerErrorMessage.value = ''
  deletingReceiptId.value = receiptId

  try {
    await deleteMinishopSale(props.book.id, receiptId)

    if (activeReceiptId.value === receiptId) {
      closeReceiptDetailDialog()
      closeReceiptPaymentDialog()
      resetSelectedReceiptState()
    }

    await refreshSelectedCustomerData()
    emit('customers-changed')
  } catch (error) {
    const didHandle = await handleReceiptActionError(error, receiptId, t('minishop.customers.unableDeleteReceipt'))

    if (!didHandle) {
      selectedCustomerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableDeleteReceipt'))
    }
  } finally {
    deletingReceiptId.value = ''
  }
}

function normalizeReceiptPaymentDiscountInput() {
  receiptPaymentForm.discountInput = formatMoney(paymentSummaryDiscountAmount.value)
}

function normalizeReceiptPaymentPaidInput() {
  receiptPaymentForm.paidInput = formatMoney(paymentSummaryPaidAmount.value)
}

async function handleUpdateReceiptPayment() {
  if (!selectedReceiptSale.value || isUpdatingReceiptPayment.value) {
    return
  }

  receiptPaymentErrorMessage.value = ''
  isUpdatingReceiptPayment.value = true
  const receiptId = selectedReceiptSale.value.id

  try {
    const { data } = await updateMinishopSalePaymentSummary(props.book.id, receiptId, {
      discount_amount: paymentSummaryDiscountAmount.value,
      paid_amount: paymentSummaryPaidAmount.value,
    })

    if (!data.sale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    selectedReceiptSale.value = data.sale
    closeReceiptPaymentDialog()
    await refreshSelectedCustomerData()
    emit('customers-changed')
  } catch (error) {
    const didHandle = await handleReceiptActionError(error, receiptId, t('minishop.customers.unableUpdateReceiptPayment'))

    if (!didHandle) {
      receiptPaymentErrorMessage.value = error instanceof Error && error.message === t('minishop.dialogs.saleResponseMissing')
        ? error.message
        : getApiErrorMessage(error, t('minishop.customers.unableUpdateReceiptPayment'))
    }
  } finally {
    isUpdatingReceiptPayment.value = false
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

async function handleReceiptActionError(error, receiptId, fallbackMessage) {
  if (isUnauthorizedError(error)) {
    closeReceiptDetailDialog()
    closeReceiptPaymentDialog()
    await router.replace({ name: 'login' })
    return true
  }

  if (isNotFoundError(error)) {
    closeReceiptDetailDialog()
    closeReceiptPaymentDialog()

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
  receiptDetailErrorMessage.value = ''
  receiptPaymentErrorMessage.value = ''
}

function isReceiptActionBusy(receiptId) {
  return deletingReceiptId.value === receiptId
    || isLoadingReceiptDetail.value
    || (isUpdatingReceiptPayment.value && activeReceiptId.value === receiptId)
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

function parseNonNegativeAmount(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue : fallback
}

function sortCustomers(items) {
  return [...items].sort((leftCustomer, rightCustomer) => {
    return String(leftCustomer.name ?? '').localeCompare(String(rightCustomer.name ?? ''))
  })
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

function formatInteger(value) {
  const amount = Number.parseInt(String(value ?? 0), 10)

  return Number.isFinite(amount) ? String(amount) : '0'
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
</script>
