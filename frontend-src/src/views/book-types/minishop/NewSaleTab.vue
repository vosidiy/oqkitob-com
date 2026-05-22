<template>
  <div class="d-flex flex-1 overflow-hidden mobile:flex-col">
    <aside class="d-flex flex-col overflow-hidden border-right  border-color-neutral-300 max-w-35% mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <select
            id="minishop-product-category-filter"
            :value="selectedCategoryId"
            class="form-select"
            :disabled="isLoadingCategories || categories.length === 0"
            @change="updateSelectedCategoryId"
          >
            <option value="">{{ $t('minishop.main.allCategories') }}</option>
            <option :value="noCategoryFilterValue">-- {{ $t('minishop.main.noCategory') }} --</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
        <div  class="flex-grow">
          <input
            type="search"
            :value="productSearchQuery"
            :placeholder="$t('minishop.main.searchProducts')"
            class="form-control"
            @input="updateProductSearchQuery"
          >
        </div>
      </header>

      <section class="overflow-y-auto scrollbar-thin py-2 flex-1">

        <div class="px-4 mb-2">
          <a href="#" role="button" class="text-secondary link" @click="emit('open-create-product')">
            + {{ $t('minishop.main.createProduct') }}
          </a>
        </div>

        <p v-if="categoryErrorMessage" class="text-red m-4">
          {{ categoryErrorMessage }}
        </p>

        <div v-if="errorMessage" class="alert alert-danger m-4" role="alert">
          {{ errorMessage }}
        </div>

        <div v-if="isLoadingProducts" class="m-3 text-secondary">{{ $t('minishop.main.loadingProducts') }}</div>

        <div v-else-if="products.length === 0" class="m-4 text-secondary">
          {{ $t('minishop.main.noProducts') }} 
        </div>

        <div v-else-if="filteredProducts.length === 0" class="m-4 p-4 text-center border-top">
          <p class="mb-2">{{ $t('minishop.main.noFilteredProducts') }}</p>
          <button type="button" class="btn text-primary" @click="emit('clear-product-filters')">
            {{ $t('minishop.main.showAllProducts') }}
          </button>
        </div>

        <ul v-else class="mb-0">
          <li
            v-for="product in filteredProducts"
            :key="product.id"
            class="border-bottom"
          >
            <div class="d-flex flex-row gap-3 align-items-center px-4 hover:bg-neutral-100">
              <div role="button" class="flex-1 overflow-hidden py-2" @click="emit('open-edit-product', product)">
                <h6 class="font-semibold mb-1">
                  {{ product.name }}
                  <span v-if="product.sku" class="text-secondary">
                    • SKU: {{ product.sku }} 
                  </span>
                </h6>
                <p>
                  {{ formatPrice(product.price) }} | {{ $t('minishop.main.quantityShort') }}: {{ formatQuantity(product.quantity) }}
                  <span v-if="isLowStock(product)" class="text-red pl-1">
                    ⚠️ {{ $t('minishop.main.low') }}
                  </span>
                </p>
              </div>

              <div>
                <button class="btn btn-neutral" @click="emit('add-product-to-cart', product)">
                  <span v-if="(cartQuantityByProductId[product.id] ?? 0) > 0">
                    {{ $t('minishop.main.added', { count: formatQuantity(cartQuantityByProductId[product.id]) }) }}
                  </span>
                  <span v-else>{{ $t('minishop.main.addToCart') }}</span>
                </button>
              </div>
            </div>
          </li>
        </ul>

        <div v-if="showClearFiltersAction && filteredProducts.length > 0" class="m-4 p-2 text-center">
          <button type="button" class="btn text-primary" @click="emit('clear-product-filters')">
            {{ $t('minishop.main.clearFilters') }}
          </button>
        </div>
      </section>
    </aside>

    <aside class="d-flex flex-col w-full h-full flex-1">
      <section class="px-4 py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center gap-2">
          <h4 class="mb-0">{{ $t('minishop.main.newSale') }}</h4>
          <div class="small text-secondary">
            {{ $t('minishop.main.itemCount', { count: cartItems.length }) }}
          </div>
        </div>
      </section>

      <section class="px-2 flex-1 overflow-y-scroll">
        <div v-if="cartItems.length === 0" class="text-secondary p-10 text-center">
          👈 {{ $t('minishop.main.emptyCart') }}
        </div>

        <div v-else class="d-flex flex-col">
          <article
            v-for="item in cartItems"
            :key="item.productId"
            class="border-bottom px-2 hover:bg-neutral-100 border-color-neutral-500"
          >
            <div class="d-flex align-items-center py-1 border-bottom">
              <h6 class="flex-1 text-lg">{{ item.name }}</h6>

              <button
                type="button"
                class="btn btn-sm btn-icon btn-plain text-red"
                @click="emit('remove-cart-item', item.productId)"
              >
                ✕
              </button>
            </div>
            <div class="row gap-x-2">
              <div class="col-auto">
                <div class="d-flex flex-row">
                  <button
                    type="button"
                    class="btn btn-icon btn-default rounded-0"
                    @click="decrementCartItem(item)"
                  >
                    ➖
                  </button>
                  <input
                    :id="`cart-quantity-${item.productId}`"
                    :value="item.quantityInput"
                    type="number"
                    class="form-control max-w-12 rounded-0"
                    min="1"
                    step="1"
                    @input="emit('update-cart-item-quantity', item.productId, $event.target.value)"
                    @blur="emit('normalize-cart-item-quantity', item.productId)"
                  >
                  <button
                    type="button"
                    class="btn btn-icon btn-default rounded-0"
                    @click="incrementCartItem(item)"
                  >
                    ➕
                  </button>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex flex-row">
                  <span class="p-2 flex-shrink-0">{{ $t('common.fields.price') }}:</span>
                  <input
                    :id="`cart-price-${item.productId}`"
                    :value="item.unitPriceInput"
                    type="number"
                    class="form-control rounded-0"
                    min="0"
                    step="1"
                    @input="emit('update-cart-item-price', item.productId, $event.target.value)"
                    @blur="emit('normalize-cart-item-price', item.productId)"
                  >
                </div>
              </div>
              <div class="col-auto flex-1">
                <p class="text-right p-2">
                  = {{ formatPrice(cartLineTotalByProductId[item.productId] ?? 0) }}
                </p>
              </div>
            </div>
          </article>
        </div>

        <div v-if="cartItems.length > 0" class="p-3 mt-3">
          <input
            type="text"
            name="note"
            :placeholder="$t('minishop.main.addNotes')"
            class="form-control"
            :value="saleNoteInput"
            @input="emit('update-sale-note-input', $event.target.value)"
          >
        </div>

        <div v-if="cartItems.length > 0" class="px-3 pb-3">
          <div class="border rounded p-3 bg-neutral-100">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2 mobile:flex-col mobile:align-items-start">
              <label class="form-label mb-0" for="minishop-cart-customer">{{ $t('common.fields.customer') }}</label>
              <button
                type="button"
                class="btn btn-default btn-sm"
                :disabled="isSavingSale"
                @click="emit('open-create-customer')"
              >
                {{ $t('minishop.main.newCustomer') }}
              </button>
            </div>

            <select
              id="minishop-cart-customer"
              :value="selectedCustomerId"
              class="form-select"
              :disabled="isLoadingCustomers || isSavingSale"
              @change="updateSelectedCustomerId"
            >
              <option value="">{{ $t('minishop.main.noCustomer') }}</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ formatCustomerOption(customer) }}
              </option>
            </select>

            <p v-if="customerErrorMessage" class="text-red text-sm mt-2 mb-0">
              {{ customerErrorMessage }}
            </p>
            <p v-else-if="isLoadingCustomers" class="text-secondary text-sm mt-2 mb-0">
              {{ $t('minishop.main.loadingCustomers') }}
            </p>
            <p v-else-if="customers.length === 0" class="text-secondary text-sm mt-2 mb-0">
              {{ $t('minishop.main.noCustomers') }}
            </p>
            <p v-else-if="selectedCustomer" class="text-secondary text-sm mt-2 mb-0">
              {{ $t('minishop.main.linkedTo', { name: selectedCustomer.name }) }}
              <span v-if="selectedCustomer.phone"> · {{ selectedCustomer.phone }}</span>
            </p>
          </div>
        </div>
      </section>

      <section class="py-2 px-4 d-flex align-items-center bg-neutral-200 justify-content-between border-top">
        <div class="d-flex align-items-center gap-2 flex-row">
          <label for="minishop-cart-discount">{{ $t('minishop.main.discountSum') }}:</label>
          <div>
            <input
              id="minishop-cart-discount"
              :value="discountInput"
              type="number"
              class="form-control max-w-40 min-h-5 h-7"
              min="0"
              step="10"
              @input="emit('update-discount-input', $event.target.value)"
              @blur="emit('normalize-discount-input')"
            >
          </div>
        </div>
        <div class="d-flex justify-content-end gap-3 align-items-center">
          <span>{{ $t('common.fields.subtotal') }}:</span>
          <strong class="text-right">{{ formatPrice(subtotal) }}</strong>
        </div>
      </section>

      <section class="px-4 py-2 border-top">
        <div v-if="saleErrorMessage" class="alert alert-danger mb-3" role="alert">
          {{ saleErrorMessage }}
        </div>

        <div class="d-flex mb-1 text-sm justify-content-end gap-3 align-items-center">
          <p class="text-secondary">{{ $t('minishop.main.discounted') }}:</p>
          <p class="text-right"> - {{ formatPrice(discountAmount) }}</p>
        </div>

        <div class="d-flex mb-1 justify-content-end gap-3 align-items-center">
          <span class="text-lg">{{ $t('minishop.main.total') }}:</span>
          <strong class="text-lg">{{ formatPrice(total) }}</strong>
        </div>

        <div class="mb-3 mt-2 gap-2 align-items-center d-flex">
          <div>
            <p v-if="paymentStatusMessage" class="font-semibold text-lg" :class="paymentStatusClass">
              {{ paymentStatusMessage }}
            </p>
          </div>
          <div class="flex-1 text-right">
            <label class="mb-0 font-semibold text-lg" for="minishop-cart-paid">{{ $t('minishop.main.paid') }}:</label>
          </div>
          <div class="col-4">
            <input
              id="minishop-cart-paid"
              :value="paidInput"
              type="number"
              class="form-control font-semibold text-lg"
              min="0"
              step="0.01"
              @input="handlePaidInput"
              @blur="emit('normalize-paid-input')"
            >
          </div>
        </div>

        <button
          type="button"
          class="btn btn-primary w-full"
          :disabled="cartItems.length === 0 || isSavingSale"
          @click="emit('complete-sale-placeholder')"
        >
          <span v-if="isSavingSale">{{ $t('common.states.saving') }}</span>
          <span v-else>{{ $t('minishop.main.completeSale') }}</span>
        </button>
      </section>
    </aside>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  cartItems: {
    type: Array,
    required: true,
  },
  cartLineTotalByProductId: {
    type: Object,
    required: true,
  },
  cartQuantityByProductId: {
    type: Object,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  categoryErrorMessage: {
    type: String,
    required: true,
  },
  customerErrorMessage: {
    type: String,
    required: true,
  },
  customers: {
    type: Array,
    required: true,
  },
  discountAmount: {
    type: Number,
    required: true,
  },
  discountInput: {
    type: String,
    required: true,
  },
  errorMessage: {
    type: String,
    required: true,
  },
  filteredProducts: {
    type: Array,
    required: true,
  },
  isLoadingCategories: {
    type: Boolean,
    required: true,
  },
  isLoadingCustomers: {
    type: Boolean,
    required: true,
  },
  isLoadingProducts: {
    type: Boolean,
    required: true,
  },
  noCategoryFilterValue: {
    type: String,
    required: true,
  },
  paidInput: {
    type: String,
    required: true,
  },
  productSearchQuery: {
    type: String,
    required: true,
  },
  saleNoteInput: {
    type: String,
    required: true,
  },
  saleErrorMessage: {
    type: String,
    required: true,
  },
  isSavingSale: {
    type: Boolean,
    required: true,
  },
  paymentStatusClass: {
    type: String,
    required: true,
  },
  paymentStatusMessage: {
    type: String,
    required: true,
  },
  products: {
    type: Array,
    required: true,
  },
  selectedCategoryId: {
    type: String,
    required: true,
  },
  selectedCustomerId: {
    type: String,
    required: true,
  },
  subtotal: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits([
  'add-product-to-cart',
  'complete-sale-placeholder',
  'normalize-cart-item-price',
  'normalize-cart-item-quantity',
  'normalize-discount-input',
  'normalize-paid-input',
  'open-create-customer',
  'open-create-product',
  'open-edit-product',
  'remove-cart-item',
  'clear-product-filters',
  'update-cart-item-price',
  'update-cart-item-quantity',
  'update:productSearchQuery',
  'update-sale-note-input',
  'update:selectedCategoryId',
  'update:selectedCustomerId',
  'update-discount-input',
  'update-paid-input',
  'mark-paid-manually-edited',
])

const selectedCustomer = computed(() => {
  return props.customers.find((customer) => customer.id === props.selectedCustomerId) ?? null
})

const showClearFiltersAction = computed(() => {
  return props.selectedCategoryId !== '' || props.productSearchQuery.trim() !== ''
})

function updateSelectedCategoryId(event) {
  emit('update:selectedCategoryId', event.target.value)
}

function updateProductSearchQuery(event) {
  emit('update:productSearchQuery', event.target.value)
}

function updateSelectedCustomerId(event) {
  emit('update:selectedCustomerId', event.target.value)
}

function handlePaidInput(event) {
  emit('mark-paid-manually-edited')
  emit('update-paid-input', event.target.value)
}

function decrementCartItem(item) {
  const currentQuantity = Number.parseFloat(String(item.quantityInput ?? '1')) || 1
  const nextQuantity = Math.max(1, currentQuantity - 1)

  emit('update-cart-item-quantity', item.productId, String(nextQuantity))
  emit('normalize-cart-item-quantity', item.productId)
}

function incrementCartItem(item) {
  const currentQuantity = Number.parseFloat(String(item.quantityInput ?? '1')) || 1
  const nextQuantity = currentQuantity + 1

  emit('update-cart-item-quantity', item.productId, String(nextQuantity))
  emit('normalize-cart-item-quantity', item.productId)
}

function formatCustomerOption(customer) {
  const name = String(customer?.name ?? '').trim()
  const phone = String(customer?.phone ?? '').trim()

  return phone !== '' ? `${name} (${phone})` : name
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
