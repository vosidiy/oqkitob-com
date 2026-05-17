<template>
  <div class="d-flex flex-1 overflow-hidden mobile:flex-col">
    <aside class="d-flex flex-col overflow-hidden border-right max-w-35% mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <button class="btn btn-primary-subtle" @click="emit('open-create-product')">
            Create product
          </button>
        </div>

        <div class="flex-grow">
          <select
            id="minishop-product-category-filter"
            :value="selectedCategoryId"
            class="form-select"
            :disabled="isLoadingCategories || categories.length === 0"
            @change="updateSelectedCategoryId"
          >
            <option value="">All categories</option>
            <option :value="noCategoryFilterValue">-- No category --</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>
      </header>

      <section class="overflow-y-auto scrollbar-thin py-2 flex-1">
        <p v-if="categoryErrorMessage" class="text-red mt-2">
          {{ categoryErrorMessage }}
        </p>

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

        <ul v-else class="mb-0">
          <li
            v-for="product in filteredProducts"
            :key="product.id"
            class="border-bottom"
          >
            <div class="d-flex align-items-center px-4 hover:bg-neutral-100">
              <div class="flex-1 overflow-hidden py-2">
                <p class="font-medium">{{ product.name }}</p>
                <p class="text-secondary">
                  {{ formatPrice(product.price) }} | Qty: {{ formatQuantity(product.quantity) }}
                  <span v-if="isLowStock(product)" class="text-red pl-1">
                    ⚠️ Low
                  </span>
                </p>
              </div>

              <div>
                <button class="btn btn-neutral" @click="emit('add-product-to-cart', product)">
                  <span v-if="(cartQuantityByProductId[product.id] ?? 0) > 0">
                    Added ({{ formatQuantity(cartQuantityByProductId[product.id]) }})
                  </span>
                  <span v-else>Add to cart</span>
                </button>
              </div>
            </div>
          </li>
        </ul>
      </section>
    </aside>

    <aside class="d-flex flex-col w-full h-full flex-1">
      <section class="px-4 py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center gap-2">
          <h4 class="mb-0">New sale</h4>
          <div class="small text-secondary">
            {{ cartItems.length }} {{ cartItems.length === 1 ? 'item' : 'items' }}
          </div>
        </div>
      </section>

      <section class="px-2 flex-1 overflow-y-scroll">
        <div v-if="cartItems.length === 0" class="text-secondary text-center min-h-100">
          Add new products
        </div>

        <div v-else class="d-flex flex-col">
          <article v-for="item in cartItems"  :key="item.productId" class="border-bottom px-2 hover:bg-neutral-100 border-color-neutral-500">
              <div class="d-flex align-items-center py-1 border-bottom">
                
                <h6 class="flex-1 text-lg">{{ item.name }}</h6>
                
                <button type="button" class="btn btn-sm btn-icon btn-plain text-red"
                  @click="emit('remove-cart-item', item.productId)">
                  ✕
                </button>

              </div>
              <div class="row  gap-x-2">
                <div class="col-auto">
                  <div class="d-flex flex-row">
                    <button class="btn btn-icon btn-default rounded-0"> ➖ </button>
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
                    <button class="btn btn-icon btn-default rounded-0"> ➕ </button>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-flex flex-row">
                    <span class="p-2 flex-shrink-0">Price:</span>
                    <input
                      :id="`cart-price-${item.productId}`"
                      :value="item.unitPriceInput"
                      type="number"
                      class="form-control rounded-0"
                      min="0"
                      step="0.01"
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
      </section>

      <section class="py-2 px-4 d-flex align-items-center bg-neutral-200 justify-content-between border-top">
        <div class="d-flex align-items-center gap-2 flex-row">
          <label for="minishop-cart-discount">Discount sum: </label>
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
          <span>Subtotal:</span>
          <strong class="text-right">{{ formatPrice(subtotal) }}</strong>
        </div>
      </section>
      <section class="px-4  py-2 border-top">
        
        <div class="d-flex mb-1 text-sm justify-content-end gap-3 align-items-center">
          <p class="text-secondary">Discounted: </p>
          <p class="text-right"> - {{ formatPrice(discountAmount) }}</p>
        </div>

        <div class="d-flex mb-1 justify-content-end gap-3 align-items-center">
          <span class="text-lg">Total: </span>
          <strong class="text-lg">{{ formatPrice(total) }}</strong>
        </div>
        
        <div class="mb-3 mt-2 gap-2 align-items-center d-flex">
          <div>
            <p v-if="paymentStatusMessage" class="font-semibold text-lg" :class="paymentStatusClass">
              {{ paymentStatusMessage }}
            </p>
          </div>
          <div class="flex-1 text-right">
            <label class="mb-0 font-semibold text-lg" for="minishop-cart-paid">Paid:</label>
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
        <button type="button" class="btn btn-primary w-full" @click="emit('complete-sale-placeholder')">
          Complete Sale
        </button>
      </section>
    </aside>
  </div>
</template>

<script setup>
defineProps({
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
  'open-create-product',
  'remove-cart-item',
  'update-cart-item-price',
  'update-cart-item-quantity',
  'update:selectedCategoryId',
  'update-discount-input',
  'update-paid-input',
  'mark-paid-manually-edited',
])

function updateSelectedCategoryId(event) {
  emit('update:selectedCategoryId', event.target.value)
}

function handlePaidInput(event) {
  emit('mark-paid-manually-edited')
  emit('update-paid-input', event.target.value)
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
