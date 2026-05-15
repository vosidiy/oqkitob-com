<template>
  <div class="row g-3">
    <div class="col-12 col-lg-3"></div>

    <div class="col-12 col-lg-6"></div>

    <div class="col-12 col-lg-3">
      <div class="border rounded bg-light p-3 h-100">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
          <div>
            <h3 class="h6 mb-1">Products</h3>
            <div class="small text-secondary">Manage product list for this shop.</div>
          </div>

          <button type="button" class="btn btn-sm btn-dark" @click="openCreateProductDialog">
            Create product
          </button>
        </div>

        <div class="mb-3">
          <label class="form-label small mb-1" for="minishop-product-category-filter">Category</label>
          <select
            id="minishop-product-category-filter"
            v-model="selectedCategoryId"
            class="form-select form-select-sm"
            :disabled="isLoadingCategories || categories.length === 0"
          >
            <option value="">All categories</option>
            <option :value="NO_CATEGORY_FILTER_VALUE">-- No category --</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <div v-if="categoryErrorMessage" class="small text-danger mt-2">
            {{ categoryErrorMessage }}
          </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
          {{ errorMessage }}
        </div>

        <div v-if="isLoadingProducts" class="text-secondary">Loading products...</div>

        <div v-else-if="products.length === 0" class="text-secondary">
          No products yet.
        </div>

        <div v-else-if="filteredProducts.length === 0" class="text-secondary">
          No products match this category.
        </div>

        <ul v-else class="list-group overflow-auto minishop-product-list mb-0">
          <li
            v-for="product in filteredProducts"
            :key="product.id"
            class="list-group-item"
          >
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div class="min-w-0">
                <div class="fw-semibold text-break">{{ product.name }}</div>
                <div class="small text-secondary">
                  {{ formatPrice(product.price) }} | Qty: {{ formatQuantity(product.quantity) }}
                </div>
              </div>

              <span v-if="isLowStock(product)" class="badge text-bg-warning">
                Low
              </span>
            </div>
          </li>
        </ul>
      </div>
    </div>

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
            class="btn btn-outline-secondary"
            @click="closeCreateProductDialog"
            :disabled="isCreatingProduct"
          >
            Cancel
          </button>
          <button type="submit" class="btn btn-dark" :disabled="isCreateProductSubmitDisabled">
            <span v-if="isCreatingProduct">Saving...</span>
            <span v-else>Create</span>
          </button>
        </div>
      </form>
    </dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import {
  createMinishopProduct,
  fetchMinishopCategories,
  fetchMinishopProducts,
} from '@/api/minishop'

const NO_CATEGORY_FILTER_VALUE = '__no_category__'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const createProductDialog = ref(null)
const products = ref([])
const categories = ref([])
const isLoadingProducts = ref(true)
const isLoadingCategories = ref(false)
const isCreatingProduct = ref(false)
const errorMessage = ref('')
const categoryErrorMessage = ref('')
const createProductErrorMessage = ref('')
const selectedCategoryId = ref('')

const createProductForm = reactive({
  name: '',
  category_id: '',
  sku: '',
  price: '',
  quantity: '',
  low_stock_alert: '',
})

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

onMounted(async () => {
  isLoadingProducts.value = true
  isLoadingCategories.value = true
  errorMessage.value = ''
  categoryErrorMessage.value = ''

  await Promise.all([
    loadProducts(),
    loadCategories(),
  ])
})

async function openCreateProductDialog() {
  createProductErrorMessage.value = ''

  if (!createProductDialog.value?.open) {
    createProductDialog.value?.showModal()
  }
}

function closeCreateProductDialog() {
  if (createProductDialog.value?.open) {
    createProductDialog.value.close()
  }
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
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateProductDialog()
      router.replace({ name: 'login' })
      return
    }

    categoryErrorMessage.value = getApiErrorMessage(error, 'Unable to load categories right now.')
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
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = 'Unable to load products.'
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

function isLowStock(product) {
  return Number(product?.is_low_stock ?? 0) === 1
}

function formatPrice(price) {
  const amount = Number(price ?? 0)

  return amount.toFixed(2)
}

function formatQuantity(quantity) {
  const formattedQuantity = Number(quantity ?? 0).toFixed(3)

  return formattedQuantity.replace(/\.?0+$/, '')
}
</script>

<style scoped>
.minishop-product-list {
  max-height: 28rem;
}
</style>
