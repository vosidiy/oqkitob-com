<template>
  <div class="d-flex flex-col h-full w-full mobile:pt-25">
    <BookPageHeader :book="book">
      <template #nav>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'main' }"
        >
          {{ $t('minishop.tabs.main') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'sales' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'sales' }"
        >
          {{ $t('minishop.tabs.sales') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'customers' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'customers' }"
        >
          {{ $t('minishop.tabs.customers') }}
        </RouterLink>
      </template>
    </BookPageHeader>

    <NewSaleTab
      v-if="activePageKey === 'main'"
      :book="book"
      :cart-items="cartItems"
      :cart-line-total-by-product-id="cartLineTotalByProductId"
      :cart-quantity-by-product-id="cartQuantityByProductId"
      :categories="categories"
      :category-error-message="categoryErrorMessage"
      :customer-error-message="customerErrorMessage"
      :customer-options="customerOptions"
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
      @open-manage-categories="openManageCategoriesDialog"
      @open-edit-product="openEditProductDialog"
      @remove-cart-item="removeCartItem"
      @update-cart-item-price="updateCartItemPrice"
      @update-cart-item-quantity="updateCartItemQuantity"
      @update:product-search-query="productSearchQuery = $event"
      @update-sale-note-input="saleNoteInput = $event"
      @update:selected-category-id="selectedCategoryId = $event"
      @update:selected-customer-id="selectedCustomerId = $event"
    />

    <SalesTab v-else-if="activePageKey === 'sales'" :book="book" />

    <CustomersTab
      v-else-if="activePageKey === 'customers'"
      :book="book"
      @customers-changed="handleCustomersChanged"
    />

    <CreateCustomerDialog
      ref="createCustomerDialog"
      :error-message="createCustomerErrorMessage"
      :form="createCustomerForm"
      :is-submit-disabled="isCreateCustomerSubmitDisabled"
      :is-submitting="isCreatingCustomer"
      name-id="create-customer-name"
      :name-placeholder="$t('minishop.customers.enterCustomerName')"
      note-id="create-customer-note"
      phone-id="create-customer-phone"
      :submit-label="$t('common.actions.create')"
      :title="$t('minishop.dialogs.createCustomer')"
      @cancel="handleCreateCustomerDialogCancel"
      @close="handleCreateCustomerDialogClose"
      @submit="handleCreateCustomer"
    />

    <CreateProductDialog
      ref="createProductDialog"
      :book="book"
      :categories="categories"
      :create-category-option-value="CREATE_CATEGORY_OPTION_VALUE"
      :error-message="createProductErrorMessage"
      :form="createProductForm"
      :is-creating-product="isCreatingProduct"
      :is-loading-categories="isLoadingCategories"
      :is-new-category-selected="isCreateProductNewCategorySelected"
      :is-submit-disabled="isCreateProductSubmitDisabled"
      @cancel="handleCreateProductDialogCancel"
      @close="handleCreateProductDialogClose"
      @open-manage-categories="openManageCategoriesDialog"
      @submit="handleCreateProduct"
    />

    <EditProductDialog
      ref="editProductDialog"
      :book="book"
      :can-deactivate="editingProductId !== ''"
      :categories="categories"
      :create-category-option-value="CREATE_CATEGORY_OPTION_VALUE"
      :error-message="editProductErrorMessage"
      :form="editProductForm"
      :is-deactivating-product="isDeactivatingProduct"
      :is-loading-categories="isLoadingCategories"
      :is-new-category-selected="isEditProductNewCategorySelected"
      :is-submit-disabled="isEditProductSubmitDisabled"
      :is-updating-product="isUpdatingProduct"
      @cancel="handleEditProductDialogCancel"
      @close="handleEditProductDialogClose"
      @deactivate="handleDeactivateProduct"
      @submit="handleUpdateProduct"
    />

    <CheckoutPaymentDialog
      ref="checkoutPaymentDialog"
      :book="book"
      :cart-items-length="cartItems.length"
      :change-amount="changeAmount"
      :discount-amount="discountAmount"
      :discount-input="discountInput"
      :error-message="saleErrorMessage"
      :is-saving-sale="isSavingSale"
      :paid-input="paidInput"
      :payment-method="paymentMethod"
      :remaining-amount="remainingAmount"
      :subtotal="subtotal"
      :total="total"
      @cancel="handleCheckoutPaymentDialogCancel"
      @close="handleCheckoutPaymentDialogClose"
      @mark-paid-manually-edited="markPaidManuallyEdited"
      @normalize-discount="normalizeDiscountInput"
      @normalize-paid="normalizePaidInput"
      @submit="handleCheckoutPaymentSubmit"
      @update:discount-input="discountInput = $event"
      @update:paid-input="paidInput = $event"
      @update:payment-method="paymentMethod = $event"
    />

    <ReceiptDialog
      ref="receiptDialog"
      :book="book"
      :receipt-state="receiptState"
      @cancel="handleReceiptDialogCancel"
      @close="handleReceiptDialogClose"
      @print="printReceipt"
    />

    <ManageCategoriesDialog
      ref="manageCategoriesDialog"
      :error-message="manageCategoriesErrorMessage"
      :is-saving="isSavingCategories"
      :is-submit-disabled="isManageCategoriesSubmitDisabled"
      :rows="manageCategoryRows"
      @add-row="appendManageCategoryRow"
      @cancel="handleManageCategoriesDialogCancel"
      @close="handleManageCategoriesDialogClose"
      @remove-row="removeManageCategoryRow"
      @submit="handleManageCategoriesSubmit"
    />
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
  fetchMinishopCustomersList,
  fetchMinishopProducts,
  manageMinishopCategories,
  updateMinishopProduct,
} from '@/api/minishop'
import BookPageHeader from '@/components/BookPageHeader.vue'
import CustomersTab from '@/views/book-types/minishop/CustomersTab.vue'
import NewSaleTab from '@/views/book-types/minishop/NewSaleTab.vue'
import SalesTab from '@/views/book-types/minishop/SalesTab.vue'
import { formatDateTime } from '@/utils/date-time'
import { formatMoneyByBookSettings } from '@/utils/money-display'
import { formatQuantityDisplay, normalizeQuantityInput } from '@/utils/quantity'
import CheckoutPaymentDialog from '@/views/book-types/minishop/dialogs/CheckoutPaymentDialog.vue'
import { createManageCategoryRow } from '@/views/book-types/minishop/dialogs/categoryRow'
import CreateCustomerDialog from '@/views/book-types/minishop/dialogs/CreateCustomerDialog.vue'
import CreateProductDialog from '@/views/book-types/minishop/dialogs/CreateProductDialog.vue'
import EditProductDialog from '@/views/book-types/minishop/dialogs/EditProductDialog.vue'
import ManageCategoriesDialog from '@/views/book-types/minishop/dialogs/ManageCategoriesDialog.vue'
import ReceiptDialog from '@/views/book-types/minishop/dialogs/ReceiptDialog.vue'

const NO_CATEGORY_FILTER_VALUE = '__no_category__'
const CREATE_CATEGORY_OPTION_VALUE = '__create_category__'
const DEFAULT_CREATE_PRODUCT_QUANTITY = '10'
const DEFAULT_CREATE_PRODUCT_LOW_STOCK_ALERT = '3'
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
const manageCategoriesDialog = ref(null)
const checkoutPaymentDialog = ref(null)
const receiptDialog = ref(null)
const products = ref([])
const categories = ref([])
const customerOptions = ref([])
const cartItems = ref([])
const isLoadingProducts = ref(false)
const isLoadingCategories = ref(false)
const isLoadingCustomers = ref(false)
const isCreatingCustomer = ref(false)
const isCreatingProduct = ref(false)
const isUpdatingProduct = ref(false)
const isDeactivatingProduct = ref(false)
const isSavingCategories = ref(false)
const isSavingSale = ref(false)
const hasLoadedMainData = ref(false)
const hasLoadedCustomerOptions = ref(false)
const isHydratingMainData = ref(false)
const errorMessage = ref('')
const categoryErrorMessage = ref('')
const customerErrorMessage = ref('')
const createCustomerErrorMessage = ref('')
const createProductErrorMessage = ref('')
const editProductErrorMessage = ref('')
const manageCategoriesErrorMessage = ref('')
const saleErrorMessage = ref('')
const selectedCategoryId = ref('')
const selectedCustomerId = ref('')
const productSearchQuery = ref('')
const discountInput = ref('0')
const paidInput = ref('0')
const paymentMethod = ref('cash')
const saleNoteInput = ref('')
const isPaidManuallyEdited = ref(false)
const receiptState = ref(null)
const editingProductId = ref('')
const manageCategoryRows = ref([])

const createProductForm = reactive({
  name: '',
  category_id: '',
  new_category_name: '',
  sku: '',
  price: '',
  quantity: DEFAULT_CREATE_PRODUCT_QUANTITY,
  low_stock_alert: DEFAULT_CREATE_PRODUCT_LOW_STOCK_ALERT,
})

const createCustomerForm = reactive({
  name: '',
  phone: '+998',
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

const isManageCategoriesSubmitDisabled = computed(() => {
  return isSavingCategories.value || manageCategoryRows.value.length === 0
})

const categoryProductCountById = computed(() => {
  return products.value.reduce((lookup, product) => {
    const categoryId = String(product?.category_id ?? '').trim()

    if (categoryId === '') {
      return lookup
    }

    lookup[categoryId] = (lookup[categoryId] ?? 0) + 1
    return lookup
  }, {})
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
    return t('minishop.main.returnChange', { amount: formatMoneyDisplay(changeAmount.value) })
  }

  if (remainingAmount.value > 0) {
    return t('minishop.main.remaining', { amount: formatMoneyDisplay(remainingAmount.value) })
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
    discountInput.value = normalizeMoneyInputValue(nextSubtotal)
  }
})

watch(total, (nextTotal) => {
  if (!isPaidManuallyEdited.value) {
    paidInput.value = normalizeMoneyInputValue(nextTotal)
    return
  }

  if (paymentMethod.value === 'card' && paidAmount.value > nextTotal) {
    paidInput.value = normalizeMoneyInputValue(nextTotal)
  }
}, { immediate: true })

watch(paymentMethod, (nextMethod) => {
  if (nextMethod === 'card' && paidAmount.value > total.value) {
    paidInput.value = normalizeMoneyInputValue(total.value)
  }
})

watch(paidAmount, (nextPaidAmount) => {
  if (paymentMethod.value === 'card' && nextPaidAmount > total.value) {
    paidInput.value = normalizeMoneyInputValue(total.value)
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
    await ensureCustomerOptionsLoaded()
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

async function ensureCustomerOptionsLoaded() {
  if (hasLoadedCustomerOptions.value || isLoadingCustomers.value) {
    return
  }

  hasLoadedCustomerOptions.value = await loadCustomerOptions()
}

async function openCreateProductDialog() {
  createProductErrorMessage.value = ''
  await ensureMainDataLoaded()
  createProductDialog.value?.open()
}

async function openManageCategoriesDialog() {
  manageCategoriesErrorMessage.value = ''
  await ensureMainDataLoaded()
  resetManageCategoryRows()
  manageCategoriesDialog.value?.open()
}

async function openEditProductDialog(product) {
  editProductErrorMessage.value = ''
  await ensureMainDataLoaded()

  editingProductId.value = String(product?.id ?? '')
  editProductForm.name = String(product?.name ?? '')
  editProductForm.category_id = String(product?.category_id ?? '')
  editProductForm.new_category_name = ''
  editProductForm.sku = String(product?.sku ?? '')
  editProductForm.price = normalizeMoneyInputValue(product?.price ?? 0)
  editProductForm.quantity = normalizeQuantityInput(product?.quantity ?? 0)
  editProductForm.low_stock_alert = product?.low_stock_alert == null
    ? ''
    : normalizeQuantityInput(product.low_stock_alert)

  editProductDialog.value?.open()
}

async function openCreateCustomerDialogFromCheckout() {
  createCustomerErrorMessage.value = ''
  await ensureCustomerOptionsLoaded()
  createCustomerDialog.value?.open()
}

function closeCreateProductDialog() {
  createProductDialog.value?.close()
}

function closeEditProductDialog() {
  editProductDialog.value?.close()
}

function closeCreateCustomerDialog() {
  createCustomerDialog.value?.close()
}

function closeManageCategoriesDialog() {
  manageCategoriesDialog.value?.close()
}

function addProductToCart(product) {
  const existingCartItem = cartItems.value.find((item) => item.productId === product.id)

  if (existingCartItem) {
    const nextQuantity = parsePositiveQuantity(existingCartItem.quantityInput, 1) + 1
    existingCartItem.quantityInput = normalizeQuantityInput(nextQuantity, 1)
    return
  }

  cartItems.value.push({
    productId: product.id,
    name: product.name,
    quantityInput: '1',
    unitPriceInput: normalizeMoneyInputValue(product.price),
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

  cartItem.quantityInput = normalizeQuantityInput(cartItem.quantityInput, 1)
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

  cartItem.unitPriceInput = clampMoneyInputValue(cartItem.unitPriceInput)
}

function removeCartItem(productId) {
  cartItems.value = cartItems.value.filter((item) => item.productId !== productId)
}

function normalizeDiscountInput() {
  discountInput.value = clampMoneyInputValue(discountInput.value, {
    max: subtotal.value,
  })
}

function markPaidManuallyEdited() {
  isPaidManuallyEdited.value = true
}

function normalizePaidInput() {
  paidInput.value = clampMoneyInputValue(paidInput.value, {
    max: paymentMethod.value === 'card' ? total.value : null,
  })
}

function openCheckoutPaymentDialog() {
  if (cartItems.value.length === 0 || isSavingSale.value) {
    return
  }

  saleErrorMessage.value = ''
  checkoutPaymentDialog.value?.open()
}

function closeCheckoutPaymentDialog() {
  checkoutPaymentDialog.value?.close()
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

function handleManageCategoriesDialogClose() {
  resetManageCategoryRows()
}

function handleManageCategoriesDialogCancel(event) {
  if (isSavingCategories.value) {
    event.preventDefault()
  }
}

function appendManageCategoryRow() {
  manageCategoryRows.value.push(createManageCategoryRow())
}

function removeManageCategoryRow(rowKey) {
  manageCategoryRows.value = manageCategoryRows.value.filter((row) => row.key !== rowKey)
}

function resetManageCategoryRows() {
  manageCategoryRows.value = categories.value.map((category) => {
    return createManageCategoryRow(category, categoryProductCountById.value[category.id] ?? 0)
  })
}

async function handleManageCategoriesSubmit() {
  if (isManageCategoriesSubmitDisabled.value) {
    return
  }

  manageCategoriesErrorMessage.value = ''
  isSavingCategories.value = true

  const hasPotentialProductCategoryChanges = manageCategoryRows.value.some((row) => {
    if (row.id === '') {
      return false
    }

    const existingCategory = categories.value.find((category) => category.id === row.id)

    return row.remove || String(existingCategory?.name ?? '') !== row.name
  })

  try {
    const { data } = await manageMinishopCategories(props.book.id, {
      categories: manageCategoryRows.value.map((row) => ({
        id: row.id,
        name: row.name,
        remove: row.remove,
      })),
    })

    categories.value = data.categories ?? []
    syncCategorySelections()

    if (hasPotentialProductCategoryChanges) {
      await loadProducts()
    }

    closeManageCategoriesDialog()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeManageCategoriesDialog()
      await router.replace({ name: 'login' })
      return
    }

    manageCategoriesErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableSaveCategories'))
  } finally {
    isSavingCategories.value = false
  }
}

function openReceiptDialog() {
  receiptDialog.value?.open()
}

function closeReceiptDialog() {
  receiptDialog.value?.close()
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
        <td>${escapeReceiptText(formatQuantityDisplay(item.quantity))}</td>
        <td>${escapeReceiptText(formatMoneyDisplay(item.unit_price))}</td>
        <td>${escapeReceiptText(formatMoneyDisplay(item.line_total))}</td>
      </tr>
    `
  }).join('')

  const statusMarkup = receipt.changeAmount > 0
    ? `
      <div class="summary-row success">
        <span>${escapeReceiptText(t('minishop.sales.returnChange'))}</span>
        <strong>${escapeReceiptText(formatMoneyDisplay(receipt.changeAmount))}</strong>
      </div>
    `
    : parseNonNegativeAmount(receipt.sale.due_amount, 0) > 0
      ? `
        <div class="summary-row warning">
          <span>${escapeReceiptText(t('minishop.sales.remainingDebt'))}</span>
          <strong>${escapeReceiptText(formatMoneyDisplay(receipt.sale.due_amount))}</strong>
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
          <div><span>${escapeReceiptText(t('common.fields.soldAt'))}</span><strong>${escapeReceiptText(formatDateTime(receipt.sale.sold_at, { locale: locale.value }))}</strong></div>
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
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.subtotal'))}</span><strong>${escapeReceiptText(formatMoneyDisplay(receipt.sale.subtotal_amount))}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.discount'))}</span><strong>- ${escapeReceiptText(formatMoneyDisplay(receipt.sale.discount_amount))}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.total'))}</span><strong>${escapeReceiptText(formatMoneyDisplay(receipt.sale.total_amount))}</strong></div>
          <div class="summary-row"><span>${escapeReceiptText(t('common.fields.paid'))}</span><strong>${escapeReceiptText(formatMoneyDisplay(receipt.tenderedAmount))}</strong></div>
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
    syncCategorySelections()
    return true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateProductDialog()
      closeEditProductDialog()
      closeManageCategoriesDialog()
      router.replace({ name: 'login' })
      return false
    }

    categoryErrorMessage.value = getApiErrorMessage(error, t('minishop.dialogs.unableLoadCategories'))
    return false
  } finally {
    isLoadingCategories.value = false
  }
}

async function loadCustomerOptions() {
  isLoadingCustomers.value = true
  customerErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopCustomersList(props.book.id)
    customerOptions.value = sortCustomers(data.customers ?? [])
    hasLoadedCustomerOptions.value = true

    if (
      selectedCustomerId.value !== ''
      && !customerOptions.value.some((customer) => customer.id === selectedCustomerId.value)
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
      closeManageCategoriesDialog()
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
  createProductForm.quantity = DEFAULT_CREATE_PRODUCT_QUANTITY
  createProductForm.low_stock_alert = DEFAULT_CREATE_PRODUCT_LOW_STOCK_ALERT
  createProductErrorMessage.value = ''
  isCreatingProduct.value = false
}

function clearProductFilters() {
  selectedCategoryId.value = ''
  productSearchQuery.value = ''
}

function resetCreateCustomerForm() {
  createCustomerForm.name = ''
  createCustomerForm.phone = '+998'
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

function syncCategorySelections() {
  const activeCategoryIds = new Set(categories.value.map((category) => String(category.id)))

  if (
    selectedCategoryId.value !== ''
    && selectedCategoryId.value !== NO_CATEGORY_FILTER_VALUE
    && !activeCategoryIds.has(selectedCategoryId.value)
  ) {
    selectedCategoryId.value = ''
  }

  if (
    createProductForm.category_id !== ''
    && createProductForm.category_id !== CREATE_CATEGORY_OPTION_VALUE
    && !activeCategoryIds.has(createProductForm.category_id)
  ) {
    createProductForm.category_id = ''
  }

  if (
    editProductForm.category_id !== ''
    && editProductForm.category_id !== CREATE_CATEGORY_OPTION_VALUE
    && !activeCategoryIds.has(editProductForm.category_id)
  ) {
    editProductForm.category_id = ''
  }
}

function upsertProduct(product) {
  const nextProducts = products.value.filter((item) => item.id !== product.id)
  nextProducts.push(product)
  products.value = sortProducts(nextProducts)
}

function upsertCustomer(customer) {
  const nextCustomers = customerOptions.value.filter((item) => item.id !== customer.id)
  nextCustomers.push(customer)
  customerOptions.value = sortCustomers(nextCustomers)
  hasLoadedCustomerOptions.value = true
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

async function handleCustomersChanged() {
  await loadCustomerOptions()
}

function findCartItem(productId) {
  return cartItems.value.find((item) => item.productId === productId) ?? null
}

function resetCheckoutState() {
  selectedCustomerId.value = ''
  discountInput.value = '0'
  paidInput.value = '0'
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

function normalizeMoneyInputValue(value) {
  const normalizedAmount = parseNonNegativeAmount(value, 0)

  return Number.isInteger(normalizedAmount) ? String(normalizedAmount) : String(normalizedAmount)
}

function clampMoneyInputValue(value, options = {}) {
  const { max = null } = options
  const trimmedValue = String(value ?? '').trim()

  if (trimmedValue === '') {
    return '0'
  }

  const parsedValue = Number.parseFloat(trimmedValue)

  if (!Number.isFinite(parsedValue) || parsedValue < 0) {
    return '0'
  }

  if (max != null && parsedValue > max) {
    return normalizeMoneyInputValue(max)
  }

  return trimmedValue
}

function formatMoneyDisplay(value) {
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
