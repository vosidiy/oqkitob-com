<template>
  <div class="d-flex flex-col h-full w-full">
    <BookPageHeader :book="book">
      <template #nav>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'main' }"
        >
          {{ $t('minishop.tabs.main') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'sales' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'sales' }"
        >
          {{ $t('minishop.tabs.sales') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'customers' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'customers' }"
        >
          {{ $t('minishop.tabs.customers') }}
        </RouterLink>
      </template>
    </BookPageHeader>

    <NewSaleTab
      v-if="activePageKey === 'main'"
      :cart-items="cartItems"
      :cart-line-total-by-product-id="cartLineTotalByProductId"
      :cart-quantity-by-product-id="cartQuantityByProductId"
      :categories="categories"
      :category-error-message="categoryErrorMessage"
      :customer-error-message="customerErrorMessage"
      :customers="customers"
      :error-message="errorMessage"
      :filtered-products="filteredProducts"
      :is-loading-categories="isLoadingCategories"
      :is-loading-customers="isLoadingCustomers"
      :is-loading-products="isLoadingProducts"
      :is-saving-sale="isSavingSale"
      :no-category-filter-value="NO_CATEGORY_FILTER_VALUE"
      :product-search-query="productSearchQuery"
      :products="products"
      :sale-note-input="saleNoteInput"
      :selected-category-id="selectedCategoryId"
      :selected-customer-id="selectedCustomerId"
      :subtotal="subtotal"
      @add-product-to-cart="addProductToCart"
      @clear-product-filters="clearProductFilters"
      @normalize-cart-item-price="normalizeCartItemPrice"
      @normalize-cart-item-quantity="normalizeCartItemQuantity"
      @open-payment-dialog="openCheckoutPaymentDialog"
      @open-create-customer="openCreateCustomerDialogFromCheckout"
      @open-create-product="openCreateProductDialog"
      @open-edit-product="openEditProductDialog"
      @remove-cart-item="removeCartItem"
      @update-cart-item-price="updateCartItemPrice"
      @update-cart-item-quantity="updateCartItemQuantity"
      @update:product-search-query="productSearchQuery = $event"
      @update-sale-note-input="saleNoteInput = $event"
      @update:selected-category-id="selectedCategoryId = $event"
      @update:selected-customer-id="selectedCustomerId = $event"
    />

    <SalesTab v-else-if="activePageKey === 'sales'" :book="book" @sales-changed="handleSalesChanged" />

    <CustomersTab
      v-else-if="activePageKey === 'customers'"
      :book="book"
      @customers-changed="handleCustomersChanged"
    />

    <dialog
      ref="createCustomerDialog"
      @cancel="handleCreateCustomerDialogCancel"
      @close="handleCreateCustomerDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.dialogs.createCustomer') }}</h5>
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
            <label class="form-label" for="create-customer-name">{{ $t('common.fields.name') }}</label>
            <input
              id="create-customer-name"
              v-model.trim="createCustomerForm.name"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.enterCustomerName')"
              :disabled="isCreatingCustomer"
              required
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="create-customer-phone">{{ $t('common.fields.phone') }}</label>
            <input
              id="create-customer-phone"
              v-model.trim="createCustomerForm.phone"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.customers.optionalPhone')"
              :disabled="isCreatingCustomer"
            >
          </div>

          <div class="mb-4">
            <label class="form-label" for="create-customer-note">{{ $t('common.fields.note') }}</label>
            <textarea
              id="create-customer-note"
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
      ref="createProductDialog"
      @cancel="handleCreateProductDialogCancel"
      @close="handleCreateProductDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.dialogs.createProduct') }}</h5>
        <button class="btn btn-icon" 
            @click="closeCreateProductDialog"
            :disabled="isCreatingProduct">
            <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <form  @submit.prevent="handleCreateProduct">
            
            <div v-if="createProductErrorMessage" class="alert alert-danger" role="alert">
              {{ createProductErrorMessage }}
            </div>

            <div class="mb-4">
              <label class="form-label" for="create-product-name">{{ $t('common.fields.name') }}</label>
              <input
                id="create-product-name"
                v-model.trim="createProductForm.name"
                type="text"
                class="form-control"
                :placeholder="$t('minishop.dialogs.enterProductName')"
                :disabled="isCreatingProduct"
                required
              >
            </div>

            <div class="row  gap-3">
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-category">{{ $t('common.fields.category') }}</label>
                <select
                  id="create-product-category"
                  v-model="createProductForm.category_id"
                  class="form-select"
                  :disabled="isCreatingProduct || isLoadingCategories"
                >
                  <option value="">--- {{ $t('minishop.main.noCategory') }} ---</option>
                  <option :value="CREATE_CATEGORY_OPTION_VALUE">+ {{ $t('minishop.dialogs.addCategory') }}</option>
                  <option
                    v-for="category in categories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.name }}
                  </option>
                </select>
                <input
                  v-if="isCreateProductNewCategorySelected"
                  id="create-product-new-category"
                  v-model.trim="createProductForm.new_category_name"
                  type="text"
                  class="form-control mt-3"
                  :placeholder="$t('minishop.dialogs.enterCategoryName')"
                  :disabled="isCreatingProduct"
                >
              </div>
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-sku">{{ $t('minishop.dialogs.productCode') }}</label>
                <input
                  id="create-product-sku"
                  v-model.trim="createProductForm.sku"
                  type="text"
                  class="form-control"
                  :placeholder="$t('minishop.dialogs.optionalSku')"
                  :disabled="isCreatingProduct"
                >
              </div>
            </div>

            <div class="row gap-3">
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-price">{{ $t('common.fields.price') }}</label>
                <input
                  id="create-product-price"
                  v-model.trim="createProductForm.price"
                  type="number"
                  class="form-control"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                  :disabled="isCreatingProduct"
                  required
                >
              </div>
              <div class="col-3 mb-4">
                <label class="form-label" for="create-product-quantity">{{ $t('common.fields.quantity') }}</label>
                <input
                  id="create-product-quantity"
                  v-model.trim="createProductForm.quantity"
                  type="number"
                  class="form-control"
                  min="0"
                  step="0.001"
                  placeholder="0"
                  :disabled="isCreatingProduct"
                  required
                >
              </div>
              <div class="col-3 mb-4">
                <label class="form-label" for="create-product-low-stock-alert">{{ $t('minishop.dialogs.lowStockAlert') }}</label>
                <input
                  id="create-product-low-stock-alert"
                  v-model.trim="createProductForm.low_stock_alert"
                  type="number"
                  class="form-control"
                  min="0"
                  step="0.001"
                  :placeholder="$t('minishop.dialogs.optionalThreshold')"
                  :disabled="isCreatingProduct"
                >
              </div>
            </div>

            <div class="pt-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isCreateProductSubmitDisabled">
                <span v-if="isCreatingProduct">{{ $t('common.states.saving') }}</span>
                <span v-else>{{ $t('common.actions.create') }}</span>
              </button>
              <button
                type="button"
                class="btn btn-default"
                @click="closeCreateProductDialog"
                :disabled="isCreatingProduct"
              >
                {{ $t('common.actions.cancel') }}
              </button>
              
            </div>
        </form>
      </div> <!-- dialog-body.// -->
    </dialog>

    <dialog
      ref="editProductDialog"
      @cancel="handleEditProductDialogCancel"
      @close="handleEditProductDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.dialogs.editProduct') }}</h5>
        <button
          class="btn btn-icon"
          @click="closeEditProductDialog"
          :disabled="isUpdatingProduct || isDeactivatingProduct"
        >
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>
      <div class="dialog-body">
        <form @submit.prevent="handleUpdateProduct">
          <div v-if="editProductErrorMessage" class="alert alert-danger" role="alert">
            {{ editProductErrorMessage }}
          </div>

          <div class="mb-4">
            <label class="form-label" for="edit-product-name">{{ $t('common.fields.name') }}</label>
              <input
                id="edit-product-name"
                v-model.trim="editProductForm.name"
                type="text"
                class="form-control"
                :placeholder="$t('minishop.dialogs.enterProductName')"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
                required
              >
          </div>

          <div class="row gap-3">
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-category">{{ $t('common.fields.category') }}</label>
              <select
                id="edit-product-category"
                v-model="editProductForm.category_id"
                class="form-select"
                :disabled="isUpdatingProduct || isDeactivatingProduct || isLoadingCategories"
              >
                <option value="">{{ $t('minishop.main.noCategory') }}</option>
                <option :value="CREATE_CATEGORY_OPTION_VALUE">+ {{ $t('minishop.dialogs.addCategory') }}</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <input
                v-if="isEditProductNewCategorySelected"
                id="edit-product-new-category"
                v-model.trim="editProductForm.new_category_name"
                type="text"
                class="form-control mt-3"
                :placeholder="$t('minishop.dialogs.enterCategoryName')"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-sku">{{ $t('minishop.dialogs.productCode') }}</label>
              <input
                id="edit-product-sku"
                v-model.trim="editProductForm.sku"
                type="text"
                class="form-control"
                :placeholder="$t('minishop.dialogs.optionalSku')"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
          </div>

          <div class="row gap-3">
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-price">{{ $t('common.fields.price') }}</label>
              <input
                id="edit-product-price"
                v-model.trim="editProductForm.price"
                type="number"
                class="form-control"
                min="0"
                step="1"
                placeholder="0.00"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
                required
              >
            </div>
            <div class="col-3 mb-4">
              <label class="form-label" for="edit-product-quantity">{{ $t('common.fields.quantity') }}</label>
              <input
                id="edit-product-quantity"
                v-model.trim="editProductForm.quantity"
                type="number"
                class="form-control"
                min="0"
                step="1"
                placeholder="0"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
                required
              >
            </div>
            <div class="col-3 mb-4">
              <label class="form-label" for="edit-product-low-stock-alert">{{ $t('minishop.dialogs.lowStockAlert') }}</label>
              <input
                id="edit-product-low-stock-alert"
                v-model.trim="editProductForm.low_stock_alert"
                type="number"
                class="form-control"
                min="0"
                step="0.001"
                :placeholder="$t('minishop.dialogs.optionalThreshold')"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
          </div>

          <div class="pt-4 d-flex justify-content-between gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isEditProductSubmitDisabled">
                <span v-if="isUpdatingProduct">{{ $t('common.states.saving') }}</span>
                <span v-else>{{ $t('common.actions.saveChanges') }}</span>
              </button>

              <button
                type="button"
                class="btn btn-default"
                @click="closeEditProductDialog"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
                {{ $t('common.actions.cancel') }}
              </button>

              <button
                type="button"
                class="btn btn-red-subtle ml-auto"
                :disabled="isUpdatingProduct || isDeactivatingProduct || editingProductId === ''"
                @click="handleDeactivateProduct"
              >
                <span v-if="isDeactivatingProduct">{{ $t('common.states.updating') }}</span>
                <span v-else>{{ $t('common.actions.deactivate') }}</span>
              </button>
              
          </div>
        </form>
      </div>
    </dialog>

    <dialog
      ref="checkoutPaymentDialog"
      class="dialog-sm"
      @cancel="handleCheckoutPaymentDialogCancel"
      @close="handleCheckoutPaymentDialogClose"
    >
      <header class="dialog-header">
        <h5>{{ $t('minishop.dialogs.paymentOverview') }}</h5>
        <button
          type="button"
          class="btn btn-icon"
          :disabled="isSavingSale"
          @click="closeCheckoutPaymentDialog"
        >
          <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
        </button>
      </header>

      <div class="dialog-body">
        <form @submit.prevent="handleCheckoutPaymentSubmit">
          <div v-if="saleErrorMessage" class="alert alert-danger mb-3" role="alert">
            {{ saleErrorMessage }}
          </div>

          <div class="d-flex justify-content-between mb-3">
            <span>{{ $t('common.fields.subtotal') }}</span>
            <div class="text-right font-semibold">
              {{ formatMoneyValue(subtotal) }}
            </div>
          </div>

          <div class="row justify-content-between mb-3">
            <label class="col-6 form-label" for="checkout-payment-discount">{{ $t('common.fields.discount') }}</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="checkout-payment-discount"
                v-model.trim="discountInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isSavingSale"
                @blur="normalizeDiscountInput"
              >
            </div>
          </div>

          <div v-if="discountAmount > 0" class="row justify-content-between mb-3">
            <span class="col-6">{{ $t('common.fields.total') }}</span>
            <div class="col-6 text-right font-semibold">
              {{ formatMoneyValue(total) }}
            </div>
          </div>

          <hr>

          <div class="mb-3">
            <label class="form-label d-block">{{ $t('common.fields.method') }}</label>
            <div class="d-flex gap-4">
              <label class="form-check d-flex align-items-center gap-2">
                <input
                  v-model="paymentMethod"
                  class="form-check-input"
                  type="radio"
                  name="checkout-payment-method"
                  value="cash"
                  :disabled="isSavingSale"
                >
                <span>{{ $t('minishop.paymentMethods.cash') }}</span>
              </label>
              <label class="form-check d-flex align-items-center gap-2">
                <input
                  v-model="paymentMethod"
                  class="form-check-input"
                  type="radio"
                  name="checkout-payment-method"
                  value="card"
                  :disabled="isSavingSale"
                >
                <span>{{ $t('minishop.paymentMethods.card') }}</span>
              </label>
            </div>
          </div>

          <div class="row justify-content-between mb-3">
            <label class="col-6 form-label" for="checkout-payment-paid">{{ $t('common.fields.paid') }}</label>
            <div class="col-6 text-right font-semibold">
              <input
                id="checkout-payment-paid"
                v-model.trim="paidInput"
                type="number"
                class="form-control min-h-5 h-8 font-semibold"
                min="0"
                step="0.01"
                :disabled="isSavingSale"
                @input="markPaidManuallyEdited"
                @blur="normalizePaidInput"
              >
            </div>
          </div>

          <div class="mb-5">
            <div
              v-if="changeAmount > 0"
              class="d-flex justify-content-between gap-3 text-green"
            >
              <span>{{ $t('minishop.sales.returnChange') }}</span>
              <strong>{{ formatMoneyValue(changeAmount) }}</strong>
            </div>
            <div
              v-else-if="remainingAmount > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>{{ $t('minishop.sales.remainingDebt') }}</span>
              <strong>{{ formatMoneyValue(remainingAmount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>{{ $t('common.fields.status') }}</span>
              <strong>{{ $t('common.states.paidInFull') }}</strong>
            </div>
          </div>

          <div class="border-top d-flex pt-4 gap-2">
            <button
              type="button"
              class="btn btn-default btn-lg flex-1"
              :disabled="isSavingSale"
              @click="closeCheckoutPaymentDialog"
            >
              {{ $t('common.actions.cancel') }}
            </button>
            <button type="submit" class="btn btn-lg btn-primary flex-1" :disabled="isSavingSale || cartItems.length === 0">
              <span v-if="isSavingSale">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('minishop.dialogs.savePayment') }}</span>
            </button>
          </div>
        </form>
      </div>
    </dialog>

    <dialog
      ref="receiptDialog"
      class="border rounded shadow p-0"
      @cancel="handleReceiptDialogCancel"
      @close="handleReceiptDialogClose"
    >
      <div class="border-bottom px-4 py-3">
        <h2 class="h5 mb-1">{{ $t('minishop.dialogs.saleSaved') }}</h2>
        <p class="text-secondary mb-0">
          {{ $t('minishop.dialogs.receiptFor', { id: receiptState?.sale?.id || book.title }) }}
        </p>
      </div>

      <div v-if="receiptState" class="px-4 py-3" id="minishop-receipt-content">
        <div class="mb-3">
          <div><strong>{{ $t('common.fields.book') }}:</strong> {{ book.title }}</div>
          <div><strong>{{ $t('common.fields.receipt') }}:</strong> {{ receiptState.sale.id }}</div>
          <div><strong>{{ $t('common.fields.soldAt') }}:</strong> {{ receiptState.sale.sold_at }}</div>
          <div><strong>{{ $t('common.fields.currency') }}:</strong> {{ receiptState.sale.currency_code }}</div>
          <div v-if="receiptState.sale.customer_name">
            <strong>{{ $t('common.fields.customer') }}:</strong>
            {{ receiptState.sale.customer_name }}
            <span v-if="receiptState.sale.customer_phone"> · {{ receiptState.sale.customer_phone }}</span>
          </div>
        </div>

        <div class="mb-3">
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
              <tr v-for="item in receiptState.items" :key="item.id">
                <td>{{ item.product_name }}</td>
                <td>{{ item.quantity }}</td>
                <td>{{ item.unit_price }}</td>
                <td>{{ item.line_total }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex flex-col gap-1">
          <div class="d-flex justify-content-between">
            <span>{{ $t('common.fields.subtotal') }}</span>
            <strong>{{ receiptState.sale.subtotal_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>{{ $t('common.fields.discount') }}</span>
            <strong>- {{ receiptState.sale.discount_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>{{ $t('common.fields.total') }}</span>
            <strong>{{ receiptState.sale.total_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>{{ $t('common.fields.paid') }}</span>
            <strong>{{ formatMoneyValue(receiptState.tenderedAmount) }}</strong>
          </div>
          <div v-if="receiptState.changeAmount > 0" class="d-flex justify-content-between text-green">
            <span>{{ $t('minishop.sales.returnChange') }}</span>
            <strong>{{ formatMoneyValue(receiptState.changeAmount) }}</strong>
          </div>
          <div v-else-if="Number(receiptState.sale.due_amount) > 0" class="d-flex justify-content-between text-orange">
            <span>{{ $t('minishop.sales.remainingDebt') }}</span>
            <strong>{{ receiptState.sale.due_amount }}</strong>
          </div>
          <div v-else class="d-flex justify-content-between text-green">
            <span>{{ $t('common.fields.status') }}</span>
            <strong>{{ $t('common.states.paidInFull') }}</strong>
          </div>
        </div>
      </div>

      <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline" @click="printReceipt">
          {{ $t('common.actions.printReceipt') }}
        </button>
        <button type="button" class="btn btn-primary" @click="closeReceiptDialog">
          {{ $t('common.actions.ok') }}
        </button>
      </div>
    </dialog>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import {
  createMinishopCustomer,
  createMinishopProduct,
  createMinishopSale,
  deactivateMinishopProduct,
  fetchMinishopCategories,
  fetchMinishopCustomers,
  fetchMinishopProducts,
  updateMinishopProduct,
} from '@/api/minishop'
import BookPageHeader from '@/components/BookPageHeader.vue'
import CustomersTab from '@/views/book-types/minishop/CustomersTab.vue'
import NewSaleTab from '@/views/book-types/minishop/NewSaleTab.vue'
import SalesTab from '@/views/book-types/minishop/SalesTab.vue'

const NO_CATEGORY_FILTER_VALUE = '__no_category__'
const CREATE_CATEGORY_OPTION_VALUE = '__create_category__'
const pageComponentByKey = {
  customers: CustomersTab,
  main: NewSaleTab,
  sales: SalesTab,
}

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()

const createCustomerDialog = ref(null)
const createProductDialog = ref(null)
const editProductDialog = ref(null)
const checkoutPaymentDialog = ref(null)
const receiptDialog = ref(null)
const products = ref([])
const categories = ref([])
const customers = ref([])
const cartItems = ref([])
const isLoadingProducts = ref(false)
const isLoadingCategories = ref(false)
const isLoadingCustomers = ref(false)
const isCreatingCustomer = ref(false)
const isCreatingProduct = ref(false)
const isUpdatingProduct = ref(false)
const isDeactivatingProduct = ref(false)
const isSavingSale = ref(false)
const hasLoadedMainData = ref(false)
const hasLoadedCustomers = ref(false)
const isHydratingMainData = ref(false)
const errorMessage = ref('')
const categoryErrorMessage = ref('')
const customerErrorMessage = ref('')
const createCustomerErrorMessage = ref('')
const createProductErrorMessage = ref('')
const editProductErrorMessage = ref('')
const saleErrorMessage = ref('')
const selectedCategoryId = ref('')
const selectedCustomerId = ref('')
const productSearchQuery = ref('')
const discountInput = ref('0.00')
const paidInput = ref('0.00')
const paymentMethod = ref('cash')
const saleNoteInput = ref('')
const isPaidManuallyEdited = ref(false)
const receiptState = ref(null)
const editingProductId = ref('')

const createProductForm = reactive({
  name: '',
  category_id: '',
  new_category_name: '',
  sku: '',
  price: '',
  quantity: '5',
  low_stock_alert: '2',
})

const createCustomerForm = reactive({
  name: '',
  phone: '',
  note: '',
})

const editProductForm = reactive({
  name: '',
  category_id: '',
  new_category_name: '',
  sku: '',
  price: '',
  quantity: '',
  low_stock_alert: '',
})

const normalizedPageParam = computed(() => String(route.params.page ?? '').trim())
const activePageKey = computed(() => normalizedPageParam.value === '' ? 'main' : normalizedPageParam.value)
const isCreateProductNewCategorySelected = computed(() => {
  return createProductForm.category_id === CREATE_CATEGORY_OPTION_VALUE
})
const isEditProductNewCategorySelected = computed(() => {
  return editProductForm.category_id === CREATE_CATEGORY_OPTION_VALUE
})
const isCreateProductSubmitDisabled = computed(() => {
  return (
    isCreatingProduct.value ||
    createProductForm.name === '' ||
    createProductForm.price === '' ||
    createProductForm.quantity === ''
  )
})

const isCreateCustomerSubmitDisabled = computed(() => {
  return isCreatingCustomer.value || createCustomerForm.name === ''
})

const isEditProductSubmitDisabled = computed(() => {
  return (
    isUpdatingProduct.value ||
    isDeactivatingProduct.value ||
    editingProductId.value === '' ||
    editProductForm.name === '' ||
    editProductForm.price === '' ||
    editProductForm.quantity === ''
  )
})

const filteredProducts = computed(() => {
  const normalizedQuery = productSearchQuery.value.trim().toLowerCase()
  let nextProducts = products.value

  if (selectedCategoryId.value === NO_CATEGORY_FILTER_VALUE) {
    nextProducts = products.value.filter((product) => {
      return (product.category_id ?? '') === ''
    })
  } else if (selectedCategoryId.value !== '') {
    nextProducts = products.value.filter((product) => product.category_id === selectedCategoryId.value)
  }

  if (normalizedQuery === '') {
    return nextProducts
  }

  return nextProducts.filter((product) => {
    const normalizedName = String(product?.name ?? '').trim().toLowerCase()
    const normalizedSku = String(product?.sku ?? '').trim().toLowerCase()

    return normalizedName.includes(normalizedQuery) || normalizedSku.includes(normalizedQuery)
  })
})

const normalizedCartItems = computed(() => {
  return cartItems.value.map((item) => {
    const quantity = parsePositiveQuantity(item.quantityInput, 1)
    const unitPrice = parseNonNegativeAmount(item.unitPriceInput, 0)

    return {
      ...item,
      quantity,
      unitPrice,
      lineTotal: quantity * unitPrice,
    }
  })
})

const cartQuantityByProductId = computed(() => {
  return normalizedCartItems.value.reduce((lookup, item) => {
    lookup[item.productId] = item.quantity
    return lookup
  }, {})
})

const cartLineTotalByProductId = computed(() => {
  return normalizedCartItems.value.reduce((lookup, item) => {
    lookup[item.productId] = item.lineTotal
    return lookup
  }, {})
})

const subtotal = computed(() => {
  return normalizedCartItems.value.reduce((sum, item) => sum + item.lineTotal, 0)
})

const discountAmount = computed(() => {
  return Math.min(parseNonNegativeAmount(discountInput.value, 0), subtotal.value)
})

const total = computed(() => {
  return Math.max(subtotal.value - discountAmount.value, 0)
})

const paidAmount = computed(() => {
  return parseNonNegativeAmount(paidInput.value, 0)
})

const changeAmount = computed(() => {
  return paidAmount.value > total.value ? paidAmount.value - total.value : 0
})

const remainingAmount = computed(() => {
  return paidAmount.value < total.value ? total.value - paidAmount.value : 0
})

const paymentStatusMessage = computed(() => {
  if (cartItems.value.length === 0) {
    return ''
  }

  if (changeAmount.value > 0) {
    return t('minishop.main.returnChange', { amount: formatMoneyValue(changeAmount.value) })
  }

  if (remainingAmount.value > 0) {
    return t('minishop.main.remaining', { amount: formatMoneyValue(remainingAmount.value) })
  }

  return t('common.states.paidInFull')
})

const paymentStatusClass = computed(() => {
  if (cartItems.value.length === 0) {
    return 'text-secondary'
  }

  if (remainingAmount.value > 0) {
    return 'text-orange'
  }

  return 'text-green'
})

const normalizedSaleItemsPayload = computed(() => {
  return normalizedCartItems.value.map((item) => ({
    product_id: item.productId,
    quantity: item.quantity,
    unit_price: item.unitPrice,
  }))
})

watch(subtotal, (nextSubtotal) => {
  if (cartItems.value.length === 0) {
    resetCheckoutState()
    return
  }

  if (parseNonNegativeAmount(discountInput.value, 0) > nextSubtotal) {
    discountInput.value = formatMoneyValue(nextSubtotal)
  }
})

watch(total, (nextTotal) => {
  if (!isPaidManuallyEdited.value) {
    paidInput.value = formatMoneyValue(nextTotal)
    return
  }

  if (paymentMethod.value === 'card' && paidAmount.value > nextTotal) {
    paidInput.value = formatMoneyValue(nextTotal)
  }
}, { immediate: true })

watch(paymentMethod, (nextMethod) => {
  if (nextMethod === 'card' && paidAmount.value > total.value) {
    paidInput.value = formatMoneyValue(total.value)
  }
})

watch(paidAmount, (nextPaidAmount) => {
  if (paymentMethod.value === 'card' && nextPaidAmount > total.value) {
    paidInput.value = formatMoneyValue(total.value)
  }
})

watch(() => createProductForm.category_id, (nextCategoryId) => {
  if (nextCategoryId !== CREATE_CATEGORY_OPTION_VALUE) {
    createProductForm.new_category_name = ''
  }
})

watch(() => editProductForm.category_id, (nextCategoryId) => {
  if (nextCategoryId !== CREATE_CATEGORY_OPTION_VALUE) {
    editProductForm.new_category_name = ''
  }
})

watch(normalizedPageParam, async (page) => {
  if (page !== '' && !(page in pageComponentByKey)) {
    await router.replace({
      name: 'book-detail',
      params: {
        bookId: props.book.id,
      },
    })
    return
  }

  if (page === '' || page === 'main') {
    await ensureMainDataLoaded()
  }

  if (page === '' || page === 'main') {
    await ensureCustomersLoaded()
  }
}, { immediate: true })

async function ensureMainDataLoaded() {
  if (hasLoadedMainData.value || isHydratingMainData.value) {
    return
  }

  isHydratingMainData.value = true

  try {
    const [didLoadProducts, didLoadCategories] = await Promise.all([
      loadProducts(),
      loadCategories(),
    ])

    hasLoadedMainData.value = didLoadProducts && didLoadCategories
  } finally {
    isHydratingMainData.value = false
  }
}

async function ensureCustomersLoaded() {
  if (hasLoadedCustomers.value || isLoadingCustomers.value) {
    return
  }

  hasLoadedCustomers.value = await loadCustomers()
}

async function openCreateProductDialog() {
  createProductErrorMessage.value = ''
  await ensureMainDataLoaded()

  if (!createProductDialog.value?.open) {
    createProductDialog.value?.showModal()
  }
}

async function openEditProductDialog(product) {
  editProductErrorMessage.value = ''
  await ensureMainDataLoaded()

  editingProductId.value = String(product?.id ?? '')
  editProductForm.name = String(product?.name ?? '')
  editProductForm.category_id = String(product?.category_id ?? '')
  editProductForm.new_category_name = ''
  editProductForm.sku = String(product?.sku ?? '')
  editProductForm.price = String(product?.price ?? '')
  editProductForm.quantity = String(product?.quantity ?? '')
  editProductForm.low_stock_alert = product?.low_stock_alert == null
    ? ''
    : String(product.low_stock_alert)

  if (!editProductDialog.value?.open) {
    editProductDialog.value?.showModal()
  }
}

async function openCreateCustomerDialogFromCheckout() {
  createCustomerErrorMessage.value = ''
  await ensureCustomersLoaded()

  if (!createCustomerDialog.value?.open) {
    createCustomerDialog.value?.showModal()
  }
}

function closeCreateProductDialog() {
  if (createProductDialog.value?.open) {
    createProductDialog.value.close()
  }
}

function closeEditProductDialog() {
  if (editProductDialog.value?.open) {
    editProductDialog.value.close()
  }
}

function closeCreateCustomerDialog() {
  if (createCustomerDialog.value?.open) {
    createCustomerDialog.value.close()
  }
}

function addProductToCart(product) {
  const existingCartItem = cartItems.value.find((item) => item.productId === product.id)

  if (existingCartItem) {
    const nextQuantity = parsePositiveQuantity(existingCartItem.quantityInput, 1) + 1
    existingCartItem.quantityInput = formatQuantityValue(nextQuantity)
    return
  }

  cartItems.value.push({
    productId: product.id,
    name: product.name,
    quantityInput: '1',
    unitPriceInput: formatMoneyValue(product.price),
  })
}

function updateCartItemQuantity(productId, rawValue) {
  const cartItem = findCartItem(productId)

  if (!cartItem) {
    return
  }

  cartItem.quantityInput = rawValue
}

function normalizeCartItemQuantity(productId) {
  const cartItem = findCartItem(productId)

  if (!cartItem) {
    return
  }

  cartItem.quantityInput = formatQuantityValue(cartItem.quantityInput)
}

function updateCartItemPrice(productId, rawValue) {
  const cartItem = findCartItem(productId)

  if (!cartItem) {
    return
  }

  cartItem.unitPriceInput = rawValue
}

function normalizeCartItemPrice(productId) {
  const cartItem = findCartItem(productId)

  if (!cartItem) {
    return
  }

  cartItem.unitPriceInput = formatMoneyValue(cartItem.unitPriceInput)
}

function removeCartItem(productId) {
  cartItems.value = cartItems.value.filter((item) => item.productId !== productId)
}

function normalizeDiscountInput() {
  discountInput.value = formatMoneyValue(discountAmount.value)
}

function markPaidManuallyEdited() {
  isPaidManuallyEdited.value = true
}

function normalizePaidInput() {
  if (paymentMethod.value === 'card' && paidAmount.value > total.value) {
    paidInput.value = formatMoneyValue(total.value)
    return
  }

  paidInput.value = formatMoneyValue(paidInput.value)
}

function openCheckoutPaymentDialog() {
  if (cartItems.value.length === 0 || isSavingSale.value) {
    return
  }

  saleErrorMessage.value = ''

  if (!checkoutPaymentDialog.value?.open) {
    checkoutPaymentDialog.value?.showModal()
  }
}

function closeCheckoutPaymentDialog() {
  if (checkoutPaymentDialog.value?.open) {
    checkoutPaymentDialog.value.close()
  }
}

function handleCheckoutPaymentDialogCancel(event) {
  if (isSavingSale.value) {
    event.preventDefault()
  }
}

function handleCheckoutPaymentDialogClose() {
  if (!isSavingSale.value) {
    saleErrorMessage.value = ''
  }
}

function handleCheckoutPaymentSubmit() {
  void saveSale()
}

function handleCreateProductDialogClose() {
  resetCreateProductForm()
}

function handleCreateProductDialogCancel(event) {
  if (isCreatingProduct.value) {
    event.preventDefault()
  }
}

function handleEditProductDialogClose() {
  resetEditProductForm()
}

function handleEditProductDialogCancel(event) {
  if (isUpdatingProduct.value || isDeactivatingProduct.value) {
    event.preventDefault()
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

function openReceiptDialog() {
  if (!receiptDialog.value?.open) {
    receiptDialog.value?.showModal()
  }
}

function closeReceiptDialog() {
  if (receiptDialog.value?.open) {
    receiptDialog.value.close()
  }
}

function handleReceiptDialogCancel(event) {
  event.preventDefault()
  closeReceiptDialog()
}

function handleReceiptDialogClose() {
  receiptState.value = null
}

async function handleSalesChanged() {
  await refreshCustomerData()
}

async function saveSale() {
  if (normalizedSaleItemsPayload.value.length === 0 || isSavingSale.value) {
    return
  }

  saleErrorMessage.value = ''
  isSavingSale.value = true

  const tenderedAmount = paidAmount.value

  try {
    const { data } = await createMinishopSale(props.book.id, {
      currency_code: 'UZS',
      customer_id: selectedCustomerId.value,
      discount_amount: discountAmount.value,
      note: normalizeOptionalInput(saleNoteInput.value),
      paid_amount: tenderedAmount,
      payment_method: paymentMethod.value,
      paid_at: makeLocalDateTimeString(),
      sold_at: makeLocalDateTimeString(),
      items: normalizedSaleItemsPayload.value,
    })

    const savedSale = data.sale ?? null
    const savedItems = Array.isArray(data.items) ? data.items : []
    const savedPayments = Array.isArray(data.payments) ? data.payments : []

    if (!savedSale) {
      throw new Error(t('minishop.dialogs.saleResponseMissing'))
    }

    receiptState.value = {
      sale: savedSale,
      items: savedItems,
      payments: savedPayments,
      tenderedAmount,
      changeAmount: Math.max(tenderedAmount - parseNonNegativeAmount(savedSale.total_amount, 0), 0),
    }

    cartItems.value = []
    resetCheckoutState()
    closeCheckoutPaymentDialog()
    await loadProducts()
    await refreshCustomerData()
    openReceiptDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCheckoutPaymentDialog()
      closeReceiptDialog()
      await router.replace({ name: 'login' })
      return
    }

    saleErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableSaveSale'))
  } finally {
    isSavingSale.value = false
  }
}

function printReceipt() {
  if (!receiptState.value) {
    return
  }

  const receiptWindow = window.open('', '_blank', 'width=720,height=900')

  if (!receiptWindow) {
    saleErrorMessage.value = t('minishop.dialogs.receiptWindowError')
    return
  }

  receiptWindow.document.write(buildReceiptHtml())
  receiptWindow.document.close()
  receiptWindow.focus()
  receiptWindow.print()
}

function buildReceiptHtml() {
  const receipt = receiptState.value

  if (!receipt) {
    return '<html><body></body></html>'
  }

  const itemRows = receipt.items.map((item) => {
    return `
      <tr>
        <td>${escapeReceiptText(item.product_name)}</td>
        <td>${escapeReceiptText(item.quantity)}</td>
        <td>${escapeReceiptText(item.unit_price)}</td>
        <td>${escapeReceiptText(item.line_total)}</td>
      </tr>
    `
  }).join('')

  const statusMarkup = receipt.changeAmount > 0
    ? `
      <div class="summary-row success">
        <span>${escapeReceiptText(t('minishop.sales.returnChange'))}</span>
        <strong>${escapeReceiptText(formatMoneyValue(receipt.changeAmount))}</strong>
      </div>
    `
    : parseNonNegativeAmount(receipt.sale.due_amount, 0) > 0
      ? `
        <div class="summary-row warning">
          <span>${escapeReceiptText(t('minishop.sales.remainingDebt'))}</span>
          <strong>${escapeReceiptText(receipt.sale.due_amount)}</strong>
        </div>
      `
      : `
        <div class="summary-row success">
          <span>${escapeReceiptText(t('common.fields.status'))}</span>
          <strong>${escapeReceiptText(t('common.states.paidInFull'))}</strong>
        </div>
      `

  return `
    <!doctype html>
    <html lang="${escapeReceiptText(locale.value)}">
      <head>
        <meta charset="utf-8">
        <title>${escapeReceiptText(t('common.fields.receipt'))} ${escapeReceiptText(receipt.sale.id)}</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            color: #111827;
            margin: 24px;
          }
          h1 {
            font-size: 22px;
            margin: 0 0 8px;
          }
          .meta,
          .summary {
            margin-top: 16px;
          }
          .meta div,
          .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 8px;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
          }
          th,
          td {
            border-bottom: 1px solid #d1d5db;
            padding: 8px 0;
            text-align: left;
          }
          .success {
            color: #166534;
          }
          .warning {
            color: #b45309;
          }
        </style>
      </head>
      <body>
        <h1>${escapeReceiptText(t('minishop.dialogs.saleReceipt'))}</h1>
        <div class="meta">
          <div><span>${escapeReceiptText(t('common.fields.book'))}</span><strong>${escapeReceiptText(props.book.title)}</strong></div>
          <div><span>${escapeReceiptText(t('common.fields.receipt'))}</span><strong>${escapeReceiptText(receipt.sale.id)}</strong></div>
          <div><span>${escapeReceiptText(t('common.fields.soldAt'))}</span><strong>${escapeReceiptText(receipt.sale.sold_at)}</strong></div>
          <div><span>${escapeReceiptText(t('common.fields.currency'))}</span><strong>${escapeReceiptText(receipt.sale.currency_code)}</strong></div>
          ${receipt.sale.customer_name
            ? `<div><span>${escapeReceiptText(t('common.fields.customer'))}</span><strong>${escapeReceiptText(receipt.sale.customer_name)}${receipt.sale.customer_phone ? ` · ${escapeReceiptText(receipt.sale.customer_phone)}` : ''}</strong></div>`
            : ''
          }
        </div>
        <table>
          <thead>
            <tr>
              <th>${escapeReceiptText(t('common.fields.item'))}</th>
              <th>${escapeReceiptText(t('minishop.main.quantityShort'))}</th>
              <th>${escapeReceiptText(t('common.fields.price'))}</th>
              <th>${escapeReceiptText(t('common.fields.total'))}</th>
            </tr>
          </thead>
          <tbody>${itemRows}</tbody>
        </table>
        <div class="summary">
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.subtotal'))}</span><strong>${escapeReceiptText(receipt.sale.subtotal_amount)}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.discount'))}</span><strong>- ${escapeReceiptText(receipt.sale.discount_amount)}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.total'))}</span><strong>${escapeReceiptText(receipt.sale.total_amount)}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.paid'))}</span><strong>${escapeReceiptText(formatMoneyValue(receipt.tenderedAmount))}</strong></div>
          ${statusMarkup}
        </div>
      </body>
    </html>
  `
}

function escapeReceiptText(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;')
}

async function loadCategories() {
  isLoadingCategories.value = true
  categoryErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCategories(props.book.id)
    categories.value = data.categories ?? []
    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateProductDialog()
      closeEditProductDialog()
      router.replace({ name: 'login' })
      return false
    }

    categoryErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableLoadCategories'))
    return false
  } finally {
    isLoadingCategories.value = false
  }
}

async function loadCustomers() {
  isLoadingCustomers.value = true
  customerErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCustomers(props.book.id)
    customers.value = sortCustomers(data.customers ?? [])
    hasLoadedCustomers.value = true

    if (
      selectedCustomerId.value !== ''
      && !customers.value.some((customer) => customer.id === selectedCustomerId.value)
    ) {
      selectedCustomerId.value = ''
    }

    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateCustomerDialog()
      router.replace({ name: 'login' })
      return false
    }

    customerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableLoadCustomers'))
    return false
  } finally {
    isLoadingCustomers.value = false
  }
}

async function loadProducts() {
  isLoadingProducts.value = true
  errorMessage.value = ''

  try {
    const { data } = await fetchMinishopProducts(props.book.id)
    products.value = sortProducts(data.products ?? [])
    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeEditProductDialog()
      router.replace({ name: 'login' })
      return false
    }

    errorMessage.value = t('minishop.dialogs.unableLoadProducts')
    return false
  } finally {
    isLoadingProducts.value = false
  }
}

async function handleCreateProduct() {
  if (isCreateProductSubmitDisabled.value) {
    return
  }

  createProductErrorMessage.value = ''
  isCreatingProduct.value = true

  try {
    const { data } = await createMinishopProduct(props.book.id, {
      name: createProductForm.name,
      category_id: isCreateProductNewCategorySelected.value ? '' : createProductForm.category_id,
      new_category_name: isCreateProductNewCategorySelected.value
        ? createProductForm.new_category_name
        : '',
      sku: createProductForm.sku,
      price: createProductForm.price,
      quantity: createProductForm.quantity,
      low_stock_alert: createProductForm.low_stock_alert,
    })

    if (data.product) {
      upsertProduct(data.product)
    }

    await loadCategories()
    closeCreateProductDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateProductDialog()
      router.replace({ name: 'login' })
      return
    }

    createProductErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableCreateProduct'))
  } finally {
    isCreatingProduct.value = false
  }
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

    upsertCustomer(data.customer)
    selectedCustomerId.value = data.customer.id
    await loadCustomers()
    closeCreateCustomerDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateCustomerDialog()
      router.replace({ name: 'login' })
      return
    }

    createCustomerErrorMessage.value = getApiErrorMessage(error, t('minishop.customers.unableCreateCustomer'))
  } finally {
    isCreatingCustomer.value = false
  }
}

async function handleUpdateProduct() {
  if (isEditProductSubmitDisabled.value) {
    return
  }

  editProductErrorMessage.value = ''
  isUpdatingProduct.value = true

  try {
    const { data } = await updateMinishopProduct(props.book.id, editingProductId.value, {
      name: editProductForm.name,
      category_id: isEditProductNewCategorySelected.value ? '' : editProductForm.category_id,
      new_category_name: isEditProductNewCategorySelected.value
        ? editProductForm.new_category_name
        : '',
      sku: editProductForm.sku,
      price: editProductForm.price,
      quantity: editProductForm.quantity,
      low_stock_alert: editProductForm.low_stock_alert,
    })

    if (data.product) {
      upsertProduct(data.product)
    }

    await loadCategories()
    closeEditProductDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeEditProductDialog()
      router.replace({ name: 'login' })
      return
    }

    editProductErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableUpdateProduct'))
  } finally {
    isUpdatingProduct.value = false
  }
}

async function handleDeactivateProduct() {
  if (editingProductId.value === '' || isUpdatingProduct.value || isDeactivatingProduct.value) {
    return
  }

  if (!window.confirm(t('minishop.dialogs.deactivateProduct'))) {
    return
  }

  editProductErrorMessage.value = ''
  isDeactivatingProduct.value = true

  try {
    await deactivateMinishopProduct(props.book.id, editingProductId.value)
    removeProductFromList(editingProductId.value)
    removeCartItem(editingProductId.value)
    closeEditProductDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeEditProductDialog()
      router.replace({ name: 'login' })
      return
    }

    editProductErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableDeactivateProduct'))
  } finally {
    isDeactivatingProduct.value = false
  }
}

function resetCreateProductForm() {
  createProductForm.name = ''
  createProductForm.category_id = ''
  createProductForm.new_category_name = ''
  createProductForm.sku = ''
  createProductForm.price = ''
  createProductForm.quantity = ''
  createProductForm.low_stock_alert = ''
  createProductErrorMessage.value = ''
  isCreatingProduct.value = false
}

function clearProductFilters() {
  selectedCategoryId.value = ''
  productSearchQuery.value = ''
}

function resetCreateCustomerForm() {
  createCustomerForm.name = ''
  createCustomerForm.phone = ''
  createCustomerForm.note = ''
  createCustomerErrorMessage.value = ''
  isCreatingCustomer.value = false
}

function resetEditProductForm() {
  editProductForm.name = ''
  editProductForm.category_id = ''
  editProductForm.new_category_name = ''
  editProductForm.sku = ''
  editProductForm.price = ''
  editProductForm.quantity = ''
  editProductForm.low_stock_alert = ''
  editProductErrorMessage.value = ''
  editingProductId.value = ''
  isUpdatingProduct.value = false
  isDeactivatingProduct.value = false
}

function upsertProduct(product) {
  const nextProducts = products.value.filter((item) => item.id !== product.id)
  nextProducts.push(product)
  products.value = sortProducts(nextProducts)
}

function upsertCustomer(customer) {
  const nextCustomers = customers.value.filter((item) => item.id !== customer.id)
  nextCustomers.push(customer)
  customers.value = sortCustomers(nextCustomers)
  hasLoadedCustomers.value = true
  customerErrorMessage.value = ''
}

function removeProductFromList(productId) {
  products.value = products.value.filter((item) => item.id !== productId)
}

function sortProducts(items) {
  return [...items].sort((leftProduct, rightProduct) => {
    return String(leftProduct.name ?? '').localeCompare(String(rightProduct.name ?? ''))
  })
}

function sortCustomers(items) {
  return [...items].sort((leftCustomer, rightCustomer) => {
    return String(leftCustomer.name ?? '').localeCompare(String(rightCustomer.name ?? ''))
  })
}

async function refreshCustomerData() {
  return loadCustomers()
}

async function handleCustomersChanged() {
  await refreshCustomerData()
}

function findCartItem(productId) {
  return cartItems.value.find((item) => item.productId === productId) ?? null
}

function resetCheckoutState() {
  selectedCustomerId.value = ''
  discountInput.value = '0.00'
  paidInput.value = '0.00'
  paymentMethod.value = 'cash'
  saleNoteInput.value = ''
  isPaidManuallyEdited.value = false
}

function normalizeOptionalInput(value) {
  const normalizedValue = String(value ?? '').trim()

  return normalizedValue === '' ? null : normalizedValue
}

function parsePositiveQuantity(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : fallback
}

function parseNonNegativeAmount(value, fallback) {
  const parsedValue = Number.parseFloat(String(value ?? '').trim())

  return Number.isFinite(parsedValue) && parsedValue >= 0 ? parsedValue : fallback
}

function formatMoneyValue(value) {
  return parseNonNegativeAmount(value, 0).toFixed(2)
}

function formatQuantityValue(value) {
  const formattedQuantity = parsePositiveQuantity(value, 1).toFixed(3)

  return formattedQuantity.replace(/\.?0+$/, '')
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
