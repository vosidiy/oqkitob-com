<template>
  <div class="d-flex flex-col h-full w-full">
    <BookPageHeader :book="book">
      <template #nav>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'main' }"
        >
          Main
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'sales' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'sales' }"
        >
          Sales
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'customers' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'customers' }"
        >
          Customers
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'reports' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0"
          :class="{ active: activePageKey === 'reports' }"
        >
          Reports
        </RouterLink>
      </template>
    </BookPageHeader>

    <MainTab
      v-if="activePageKey === 'main'"
      :cart-items="cartItems"
      :cart-line-total-by-product-id="cartLineTotalByProductId"
      :cart-quantity-by-product-id="cartQuantityByProductId"
      :categories="categories"
      :category-error-message="categoryErrorMessage"
      :discount-amount="discountAmount"
      :discount-input="discountInput"
      :error-message="errorMessage"
      :filtered-products="filteredProducts"
      :is-loading-categories="isLoadingCategories"
      :is-loading-products="isLoadingProducts"
      :is-saving-sale="isSavingSale"
      :no-category-filter-value="NO_CATEGORY_FILTER_VALUE"
      :paid-input="paidInput"
      :payment-status-class="paymentStatusClass"
      :payment-status-message="paymentStatusMessage"
      :products="products"
      :sale-note-input="saleNoteInput"
      :sale-error-message="saleErrorMessage"
      :selected-category-id="selectedCategoryId"
      :subtotal="subtotal"
      :total="total"
      @add-product-to-cart="addProductToCart"
      @complete-sale-placeholder="handleCompleteSalePlaceholder"
      @normalize-cart-item-price="normalizeCartItemPrice"
      @normalize-cart-item-quantity="normalizeCartItemQuantity"
      @normalize-discount-input="normalizeDiscountInput"
      @normalize-paid-input="normalizePaidInput"
      @open-create-product="openCreateProductDialog"
      @open-edit-product="openEditProductDialog"
      @remove-cart-item="removeCartItem"
      @update-cart-item-price="updateCartItemPrice"
      @update-cart-item-quantity="updateCartItemQuantity"
      @update-sale-note-input="saleNoteInput = $event"
      @update:selected-category-id="selectedCategoryId = $event"
      @update-discount-input="discountInput = $event"
      @update-paid-input="paidInput = $event"
      @mark-paid-manually-edited="markPaidManuallyEdited"
    />

    <SalesTab v-else-if="activePageKey === 'sales'" :book="book" />

    <CustomersTab v-else-if="activePageKey === 'customers'" />

    <ReportsTab v-else-if="activePageKey === 'reports'" />

    <dialog
      ref="createProductDialog"
      @cancel="handleCreateProductDialogCancel"
      @close="handleCreateProductDialogClose"
    >
      <header class="dialog-header">
        <h5>Create product</h5>
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
              <label class="form-label" for="create-product-name">Name</label>
              <input
                id="create-product-name"
                v-model.trim="createProductForm.name"
                type="text"
                class="form-control"
                placeholder="Enter product name"
                :disabled="isCreatingProduct"
                required
              >
            </div>

            <div class="row  gap-3">
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-category">Category</label>
                <select
                  id="create-product-category"
                  v-model="createProductForm.category_id"
                  class="form-select"
                  :disabled="isCreatingProduct || isLoadingCategories"
                >
                  <option value="">--- No category ---</option>
                  <option :value="CREATE_CATEGORY_OPTION_VALUE">+ Add new category</option>
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
                  placeholder="Enter new category name"
                  :disabled="isCreatingProduct"
                >
              </div>
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-sku">SKU / Kod</label>
                <input
                  id="create-product-sku"
                  v-model.trim="createProductForm.sku"
                  type="text"
                  class="form-control"
                  placeholder="Optional SKU"
                  :disabled="isCreatingProduct"
                >
              </div>
            </div>

            <div class="row gap-3">
              <div class="col-6 mb-4">
                <label class="form-label" for="create-product-price">Price</label>
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
                <label class="form-label" for="create-product-quantity">Quantity</label>
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
                <label class="form-label" for="create-product-low-stock-alert">Low-stock alert</label>
                <input
                  id="create-product-low-stock-alert"
                  v-model.trim="createProductForm.low_stock_alert"
                  type="number"
                  class="form-control"
                  min="0"
                  step="0.001"
                  placeholder="Optional threshold"
                  :disabled="isCreatingProduct"
                >
              </div>
            </div>

            <div class="pt-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isCreateProductSubmitDisabled">
                <span v-if="isCreatingProduct">Saving...</span>
                <span v-else>Create</span>
              </button>
              <button
                type="button"
                class="btn btn-default"
                @click="closeCreateProductDialog"
                :disabled="isCreatingProduct"
              >
                Cancel
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
        <h5>Edit product</h5>
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
            <label class="form-label" for="edit-product-name">Name</label>
            <input
              id="edit-product-name"
              v-model.trim="editProductForm.name"
              type="text"
              class="form-control"
              placeholder="Enter product name"
              :disabled="isUpdatingProduct || isDeactivatingProduct"
              required
            >
          </div>

          <div class="row gap-3">
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-category">Category</label>
              <select
                id="edit-product-category"
                v-model="editProductForm.category_id"
                class="form-select"
                :disabled="isUpdatingProduct || isDeactivatingProduct || isLoadingCategories"
              >
                <option value="">No category</option>
                <option :value="CREATE_CATEGORY_OPTION_VALUE">+ Add new category</option>
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
                placeholder="Enter new category name"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-sku">SKU / Kod</label>
              <input
                id="edit-product-sku"
                v-model.trim="editProductForm.sku"
                type="text"
                class="form-control"
                placeholder="Optional SKU"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
          </div>

          <div class="row gap-3">
            <div class="col-6 mb-4">
              <label class="form-label" for="edit-product-price">Price</label>
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
              <label class="form-label" for="edit-product-quantity">Quantity</label>
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
              <label class="form-label" for="edit-product-low-stock-alert">Low-stock alert</label>
              <input
                id="edit-product-low-stock-alert"
                v-model.trim="editProductForm.low_stock_alert"
                type="number"
                class="form-control"
                min="0"
                step="0.001"
                placeholder="Optional threshold"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
            </div>
          </div>

          <div class="pt-4 d-flex justify-content-between gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isEditProductSubmitDisabled">
                <span v-if="isUpdatingProduct">Saving...</span>
                <span v-else>Save changes</span>
              </button>

              <button
                type="button"
                class="btn btn-default"
                @click="closeEditProductDialog"
                :disabled="isUpdatingProduct || isDeactivatingProduct"
              >
                Cancel
              </button>

              <button
                type="button"
                class="btn btn-red-subtle ml-auto"
                :disabled="isUpdatingProduct || isDeactivatingProduct || editingProductId === ''"
                @click="handleDeactivateProduct"
              >
                <span v-if="isDeactivatingProduct">Deactivating...</span>
                <span v-else>Deactivate</span>
              </button>
              
          </div>
        </form>
      </div>
    </dialog>

    <dialog
      ref="debtConfirmDialog"
      class="border rounded shadow p-0"
      @cancel="handleDebtConfirmDialogCancel"
      @close="handleDebtConfirmDialogClose"
    >
      <div class="border-bottom px-4 py-3">
        <h2 class="h5 mb-1">Confirm debt sale</h2>
        <p class="text-secondary mb-0">
          This sale still has {{ formatMoneyValue(remainingAmount) }} remaining.
        </p>
      </div>

      <div class="px-4 py-3">
        <p class="mb-0">Continue and save this sale as debt?</p>
      </div>

      <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
        <button
          type="button"
          class="btn btn-outline"
          :disabled="isSavingSale"
          @click="closeDebtConfirmDialog"
        >
          Cancel
        </button>
        <button
          type="button"
          class="btn btn-primary"
          :disabled="isSavingSale"
          @click="confirmDebtSale"
        >
          <span v-if="isSavingSale">Saving...</span>
          <span v-else>Confirm</span>
        </button>
      </div>
    </dialog>

    <dialog
      ref="receiptDialog"
      class="border rounded shadow p-0"
      @cancel="handleReceiptDialogCancel"
      @close="handleReceiptDialogClose"
    >
      <div class="border-bottom px-4 py-3">
        <h2 class="h5 mb-1">Sale saved</h2>
        <p class="text-secondary mb-0">
          Receipt for {{ receiptState?.sale?.id || 'this sale' }}
        </p>
      </div>

      <div v-if="receiptState" class="px-4 py-3" id="minishop-receipt-content">
        <div class="mb-3">
          <div><strong>Book:</strong> {{ book.title }}</div>
          <div><strong>Receipt:</strong> {{ receiptState.sale.id }}</div>
          <div><strong>Sold at:</strong> {{ receiptState.sale.sold_at }}</div>
          <div><strong>Currency:</strong> {{ receiptState.sale.currency_code }}</div>
        </div>

        <div class="mb-3">
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
            <span>Subtotal</span>
            <strong>{{ receiptState.sale.subtotal_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Discount</span>
            <strong>- {{ receiptState.sale.discount_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Total</span>
            <strong>{{ receiptState.sale.total_amount }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Paid</span>
            <strong>{{ formatMoneyValue(receiptState.tenderedAmount) }}</strong>
          </div>
          <div v-if="receiptState.changeAmount > 0" class="d-flex justify-content-between text-green">
            <span>Return change</span>
            <strong>{{ formatMoneyValue(receiptState.changeAmount) }}</strong>
          </div>
          <div v-else-if="Number(receiptState.sale.due_amount) > 0" class="d-flex justify-content-between text-orange">
            <span>Remaining debt</span>
            <strong>{{ receiptState.sale.due_amount }}</strong>
          </div>
          <div v-else class="d-flex justify-content-between text-green">
            <span>Status</span>
            <strong>Paid in full</strong>
          </div>
        </div>
      </div>

      <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline" @click="printReceipt">
          Print receipt
        </button>
        <button type="button" class="btn btn-primary" @click="closeReceiptDialog">
          OK
        </button>
      </div>
    </dialog>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import {
  createMinishopProduct,
  createMinishopSale,
  deactivateMinishopProduct,
  fetchMinishopCategories,
  fetchMinishopProducts,
  updateMinishopProduct,
} from '@/api/minishop'
import BookPageHeader from '@/components/BookPageHeader.vue'
import CustomersTab from '@/views/book-types/minishop/CustomersTab.vue'
import MainTab from '@/views/book-types/minishop/MainTab.vue'
import ReportsTab from '@/views/book-types/minishop/ReportsTab.vue'
import SalesTab from '@/views/book-types/minishop/SalesTab.vue'

const NO_CATEGORY_FILTER_VALUE = '__no_category__'
const CREATE_CATEGORY_OPTION_VALUE = '__create_category__'
const pageComponentByKey = {
  customers: CustomersTab,
  main: MainTab,
  reports: ReportsTab,
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

const createProductDialog = ref(null)
const editProductDialog = ref(null)
const debtConfirmDialog = ref(null)
const receiptDialog = ref(null)
const products = ref([])
const categories = ref([])
const cartItems = ref([])
const isLoadingProducts = ref(false)
const isLoadingCategories = ref(false)
const isCreatingProduct = ref(false)
const isUpdatingProduct = ref(false)
const isDeactivatingProduct = ref(false)
const isSavingSale = ref(false)
const hasLoadedMainData = ref(false)
const isHydratingMainData = ref(false)
const errorMessage = ref('')
const categoryErrorMessage = ref('')
const createProductErrorMessage = ref('')
const editProductErrorMessage = ref('')
const saleErrorMessage = ref('')
const selectedCategoryId = ref('')
const discountInput = ref('0.00')
const paidInput = ref('0.00')
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
  if (selectedCategoryId.value === '') {
    return products.value
  }

  if (selectedCategoryId.value === NO_CATEGORY_FILTER_VALUE) {
    return products.value.filter((product) => {
      return (product.category_id ?? '') === ''
    })
  }

  return products.value.filter((product) => product.category_id === selectedCategoryId.value)
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
    return `Return change: ${formatMoneyValue(changeAmount.value)}`
  }

  if (remainingAmount.value > 0) {
    return `Remaining: ${formatMoneyValue(remainingAmount.value)}`
  }

  return 'Paid in full'
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
  }
}, { immediate: true })

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
  paidInput.value = formatMoneyValue(paidInput.value)
}

function handleCompleteSalePlaceholder() {
  if (cartItems.value.length === 0 || isSavingSale.value) {
    return
  }

  saleErrorMessage.value = ''

  if (remainingAmount.value > 0) {
    openDebtConfirmDialog()
    return
  }

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

function openDebtConfirmDialog() {
  if (!debtConfirmDialog.value?.open) {
    debtConfirmDialog.value?.showModal()
  }
}

function closeDebtConfirmDialog() {
  if (debtConfirmDialog.value?.open) {
    debtConfirmDialog.value.close()
  }
}

function handleDebtConfirmDialogCancel(event) {
  if (isSavingSale.value) {
    event.preventDefault()
  }
}

function handleDebtConfirmDialogClose() {
  if (!isSavingSale.value) {
    saleErrorMessage.value = ''
  }
}

function confirmDebtSale() {
  void saveSale()
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
      discount_amount: discountAmount.value,
      note: normalizeOptionalInput(saleNoteInput.value),
      paid_amount: tenderedAmount,
      sold_at: makeLocalDateTimeString(),
      items: normalizedSaleItemsPayload.value,
    })

    const savedSale = data.sale ?? null
    const savedItems = Array.isArray(data.items) ? data.items : []

    if (!savedSale) {
      throw new Error('Sale response did not include receipt data.')
    }

    receiptState.value = {
      sale: savedSale,
      items: savedItems,
      tenderedAmount,
      changeAmount: Math.max(tenderedAmount - parseNonNegativeAmount(savedSale.total_amount, 0), 0),
    }

    cartItems.value = []
    resetCheckoutState()
    closeDebtConfirmDialog()
    await loadProducts()
    openReceiptDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeDebtConfirmDialog()
      closeReceiptDialog()
      await router.replace({ name: 'login' })
      return
    }

    saleErrorMessage.value = getApiErrorMessage(error, 'Unable to save sale right now.')
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
    saleErrorMessage.value = 'Unable to open the receipt preview window.'
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
        <span>Return change</span>
        <strong>${escapeReceiptText(formatMoneyValue(receipt.changeAmount))}</strong>
      </div>
    `
    : parseNonNegativeAmount(receipt.sale.due_amount, 0) > 0
      ? `
        <div class="summary-row warning">
          <span>Remaining debt</span>
          <strong>${escapeReceiptText(receipt.sale.due_amount)}</strong>
        </div>
      `
      : `
        <div class="summary-row success">
          <span>Status</span>
          <strong>Paid in full</strong>
        </div>
      `

  return `
    <!doctype html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <title>Receipt ${escapeReceiptText(receipt.sale.id)}</title>
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
        <h1>Sale receipt</h1>
        <div class="meta">
          <div><span>Book</span><strong>${escapeReceiptText(props.book.title)}</strong></div>
          <div><span>Receipt</span><strong>${escapeReceiptText(receipt.sale.id)}</strong></div>
          <div><span>Sold at</span><strong>${escapeReceiptText(receipt.sale.sold_at)}</strong></div>
          <div><span>Currency</span><strong>${escapeReceiptText(receipt.sale.currency_code)}</strong></div>
        </div>
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>${itemRows}</tbody>
        </table>
        <div class="summary">
          <div class="summary-row"><span>Subtotal</span><strong>${escapeReceiptText(receipt.sale.subtotal_amount)}</strong></div>
          <div class="summary-row"><span>Discount</span><strong>- ${escapeReceiptText(receipt.sale.discount_amount)}</strong></div>
          <div class="summary-row"><span>Total</span><strong>${escapeReceiptText(receipt.sale.total_amount)}</strong></div>
          <div class="summary-row"><span>Paid</span><strong>${escapeReceiptText(formatMoneyValue(receipt.tenderedAmount))}</strong></div>
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

    categoryErrorMessage.value = getApiErrorMessage(error, 'Unable to load categories right now.')
    return false
  } finally {
    isLoadingCategories.value = false
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

    errorMessage.value = 'Unable to load products.'
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

    createProductErrorMessage.value = getApiErrorMessage(error, 'Unable to create product right now.')
  } finally {
    isCreatingProduct.value = false
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

    editProductErrorMessage.value = getApiErrorMessage(error, 'Unable to update product right now.')
  } finally {
    isUpdatingProduct.value = false
  }
}

async function handleDeactivateProduct() {
  if (editingProductId.value === '' || isUpdatingProduct.value || isDeactivatingProduct.value) {
    return
  }

  if (!window.confirm('Deactivate this product?')) {
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

    editProductErrorMessage.value = getApiErrorMessage(error, 'Unable to deactivate product right now.')
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

function removeProductFromList(productId) {
  products.value = products.value.filter((item) => item.id !== productId)
}

function sortProducts(items) {
  return [...items].sort((leftProduct, rightProduct) => {
    return String(leftProduct.name ?? '').localeCompare(String(rightProduct.name ?? ''))
  })
}

function findCartItem(productId) {
  return cartItems.value.find((item) => item.productId === productId) ?? null
}

function resetCheckoutState() {
  discountInput.value = '0.00'
  paidInput.value = '0.00'
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
