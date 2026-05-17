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
      :no-category-filter-value="NO_CATEGORY_FILTER_VALUE"
      :paid-input="paidInput"
      :payment-status-class="paymentStatusClass"
      :payment-status-message="paymentStatusMessage"
      :products="products"
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
      @remove-cart-item="removeCartItem"
      @update-cart-item-price="updateCartItemPrice"
      @update-cart-item-quantity="updateCartItemQuantity"
      @update:selected-category-id="selectedCategoryId = $event"
      @update-discount-input="discountInput = $event"
      @update-paid-input="paidInput = $event"
      @mark-paid-manually-edited="markPaidManuallyEdited"
    />

    <SalesTab v-else-if="activePageKey === 'sales'" />

    <ReportsTab v-else-if="activePageKey === 'reports'" />

    <dialog
      ref="createProductDialog"
      class="border rounded shadow p-0"
      @cancel="handleCreateProductDialogCancel"
      @close="handleCreateProductDialogClose"
    >
      <form class="m-0" @submit.prevent="handleCreateProduct">
        <div class="border-bottom px-4 py-3">
          <h2 class="h5 mb-1">Create product</h2>
          <p class="text-secondary mb-0">Add a product to this minishop book.</p>
        </div>

        <div class="px-4 py-3">
          <div v-if="createProductErrorMessage" class="alert alert-danger" role="alert">
            {{ createProductErrorMessage }}
          </div>

          <div class="mb-3">
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

          <div class="mb-3">
            <label class="form-label" for="create-product-category">Category</label>
            <select
              id="create-product-category"
              v-model="createProductForm.category_id"
              class="form-select"
              :disabled="isCreatingProduct || isLoadingCategories"
            >
              <option value="">No category</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" for="create-product-sku">SKU</label>
            <input
              id="create-product-sku"
              v-model.trim="createProductForm.sku"
              type="text"
              class="form-control"
              placeholder="Optional SKU"
              :disabled="isCreatingProduct"
            >
          </div>

          <div class="row g-3">
            <div class="col-12 col-sm-6">
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

            <div class="col-12 col-sm-6">
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
          </div>

          <div class="mt-3 mb-0">
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

        <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
          <button
            type="button"
            class="btn btn-outline"
            @click="closeCreateProductDialog"
            :disabled="isCreatingProduct"
          >
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isCreateProductSubmitDisabled">
            <span v-if="isCreatingProduct">Saving...</span>
            <span v-else>Create</span>
          </button>
        </div>
      </form>
    </dialog>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import {
  createMinishopProduct,
  fetchMinishopCategories,
  fetchMinishopProducts,
} from '@/api/minishop'
import BookPageHeader from '@/components/BookPageHeader.vue'
import MainTab from '@/views/book-types/minishop/MainTab.vue'
import ReportsTab from '@/views/book-types/minishop/ReportsTab.vue'
import SalesTab from '@/views/book-types/minishop/SalesTab.vue'

const NO_CATEGORY_FILTER_VALUE = '__no_category__'
const pageComponentByKey = {
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
const products = ref([])
const categories = ref([])
const cartItems = ref([])
const isLoadingProducts = ref(false)
const isLoadingCategories = ref(false)
const isCreatingProduct = ref(false)
const hasLoadedMainData = ref(false)
const isHydratingMainData = ref(false)
const errorMessage = ref('')
const categoryErrorMessage = ref('')
const createProductErrorMessage = ref('')
const selectedCategoryId = ref('')
const discountInput = ref('0.00')
const paidInput = ref('0.00')
const isPaidManuallyEdited = ref(false)

const createProductForm = reactive({
  name: '',
  category_id: '',
  sku: '',
  price: '',
  quantity: '',
  low_stock_alert: '',
})

const normalizedPageParam = computed(() => String(route.params.page ?? '').trim())
const activePageKey = computed(() => normalizedPageParam.value === '' ? 'main' : normalizedPageParam.value)
const isCreateProductSubmitDisabled = computed(() => {
  return (
    isCreatingProduct.value ||
    createProductForm.name === '' ||
    createProductForm.price === '' ||
    createProductForm.quantity === ''
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

function closeCreateProductDialog() {
  if (createProductDialog.value?.open) {
    createProductDialog.value.close()
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
  window.alert('Complete Sale will be connected to backend later.')
}

function handleCreateProductDialogClose() {
  resetCreateProductForm()
}

function handleCreateProductDialogCancel(event) {
  if (isCreatingProduct.value) {
    event.preventDefault()
  }
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
    products.value = data.products ?? []
    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
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
      category_id: createProductForm.category_id,
      sku: createProductForm.sku,
      price: createProductForm.price,
      quantity: createProductForm.quantity,
      low_stock_alert: createProductForm.low_stock_alert,
    })

    if (data.product) {
      products.value = [...products.value, data.product].sort((leftProduct, rightProduct) => {
        return String(leftProduct.name ?? '').localeCompare(String(rightProduct.name ?? ''))
      })
    }

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

function resetCreateProductForm() {
  createProductForm.name = ''
  createProductForm.category_id = ''
  createProductForm.sku = ''
  createProductForm.price = ''
  createProductForm.quantity = ''
  createProductForm.low_stock_alert = ''
  createProductErrorMessage.value = ''
  isCreatingProduct.value = false
}

function findCartItem(productId) {
  return cartItems.value.find((item) => item.productId === productId) ?? null
}

function resetCheckoutState() {
  discountInput.value = '0.00'
  paidInput.value = '0.00'
  isPaidManuallyEdited.value = false
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
</script>
