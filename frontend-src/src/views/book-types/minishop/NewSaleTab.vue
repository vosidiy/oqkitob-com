<template>
  <div class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
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

      <section class="overflow-y-auto scrollbar-thin py-2 flex-1 mobile:pb-20">

        <div class="px-4  mb-2">
          <a href="#" role="button" class="mr-3 link" @click="emit('open-create-product')">
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

        <div v-else-if="products.length === 0" class="m-5 text-secondary text-lg text-center p-3">
          <p class="mb-3"> 📦 {{ $t('minishop.main.noProducts') }} </p>

          <a href="#" role="button" class="btn btn-primary" @click="emit('open-create-product')">
            + {{ $t('minishop.main.createProduct') }}
          </a>
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
                  {{ formatPrice(product.price) }} <small class="currency-code">{{ props.book.currency_code }}</small> | {{ $t('minishop.main.quantityShort') }}: {{ formatQuantity(product.quantity) }}
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

        <p class="px-4 mt-4 text-center">
          <a href="#" role="button" class="text-secondary link" @click.prevent="emit('open-manage-categories')"> {{ $t('minishop.main.categories') }}  </a>
        </p>
      </section>
    </aside>

    <aside
      class="d-flex flex-col w-full h-full  mobile:pt-25  flex-1 bg-base mobile:fixed top-0 left-0 right-0 bottom-0 z-20"
      :class="{ 'mobile:d-flex': isMobileCartOpen, 'mobile:d-none': !isMobileCartOpen }"
    >
      <section class="px-4 py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center gap-2">
          <div>
            <h4 class="mb-0">{{ $t('minishop.main.newSale') }}</h4>
            <div class="small text-secondary">
              {{ $t('minishop.main.itemCount', { count: cartItems.length }) }}
            </div>
          </div>

          <button
            type="button"
            class="btn btn-default d-none mobile:d-flex"
            :title="$t('common.actions.close')"
            @click="closeMobileCart"
          >
            {{ $t('common.actions.back') }}
          </button>
        </div>
      </section>

      <section class="px-2 flex-1 overflow-y-scroll">
        <div v-if="cartItems.length === 0" class="text-secondary p-10 text-lg text-center">
          👈 {{ $t('minishop.main.emptyCart') }}
        </div>

        <div v-else class="d-flex flex-col">
          <article
            v-for="item in cartItems"
            :key="item.productId"
            class="border-bottom px-2 hover:bg-neutral-100 border-color-neutral-500"
          >
            <div class="d-flex align-items-center py-1 border-bottom">
              <h6 class="flex-1 text-lg"> 📦 {{ item.name }}</h6>

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
                  = {{ formatPrice(cartLineTotalByProductId[item.productId] ?? 0) }} <small class="currency-code">{{ props.book.currency_code }}</small>
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
            <div class="d-flex gap-2 mb-2">
              <v-select
                v-model="selectedCustomerIdModel"
                input-id="minishop-cart-customer"
                class="flex-1"
                :class="{ 'is-disabled': isLoadingCustomers || isSavingSale }"
                :clearable="true"
                :disabled="isLoadingCustomers || isSavingSale"
                :options="customerOptionsWithSearchLabel"
                :placeholder="$t('minishop.main.noCustomer')"
                :reduce="(customer) => customer.id"
                label="search_label"
              />

              <button
                type="button"
                class="btn btn-default"
                :disabled="isSavingSale"
                @click="emit('open-create-customer')"
              >
                👤 {{ $t('minishop.main.newCustomer') }}
              </button>

            </div>

            <div>
              <p v-if="customerErrorMessage" class="text-red">
                {{ customerErrorMessage }}
              </p>
              <p v-else-if="isLoadingCustomers" class="text-secondary">
                {{ $t('minishop.main.loadingCustomers') }}
              </p>
              <p v-else-if="customerOptions.length === 0" class="text-secondary">
                {{ $t('minishop.main.noCustomers') }}
              </p>
              <p v-else-if="selectedCustomer" class="text-secondary">
                {{ $t('minishop.main.linkedTo', { name: selectedCustomer.name }) }}
                <span v-if="selectedCustomer.phone"> · {{ selectedCustomer.phone }}</span>
              </p>
            </div>
          </div>
          
        </div>
      </section>

      <section class="px-4 py-3 border-top bg-neutral-200">
        <div class="d-flex mb-3 justify-content-end gap-3 align-items-center">
          <span class="text-lg">{{ $t('minishop.main.total') }}:</span>
          <strong class="text-lg">{{ formatPrice(subtotal) }} <small class="currency-code">{{ props.book.currency_code }}</small></strong>
        </div>

        <button
          type="button"
          class="btn btn-primary w-full"
          :disabled="cartItems.length === 0 || isSavingSale"
          @click="emit('open-payment-dialog')"
        >
          <span v-if="isSavingSale">{{ $t('common.states.saving') }}</span>
          <span v-else>{{ $t('minishop.main.completeSale') }}</span>
        </button>
      </section>
    </aside>

    <div class="d-none mobile:d-block fixed left-0 right-0 bottom-0 z-10 bg-base border-top border-color-neutral-300 p-3">
      <button type="button" class="btn btn-primary w-full" @click="openMobileCart">
        {{ $t('minishop.main.selectedCart', { count: cartItems.length }) }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import vSelect from 'vue-select'
import { formatMoneyByBookSettings } from '@/utils/money-display'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
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
  customerOptions: {
    type: Array,
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
  productSearchQuery: {
    type: String,
    required: true,
  },
  saleNoteInput: {
    type: String,
    required: true,
  },
  isSavingSale: {
    type: Boolean,
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
})

const emit = defineEmits([
  'add-product-to-cart',
  'clear-product-filters',
  'normalize-cart-item-price',
  'normalize-cart-item-quantity',
  'open-payment-dialog',
  'open-create-customer',
  'open-create-product',
  'open-manage-categories',
  'open-edit-product',
  'remove-cart-item',
  'update-cart-item-price',
  'update-cart-item-quantity',
  'update:productSearchQuery',
  'update-sale-note-input',
  'update:selectedCategoryId',
  'update:selectedCustomerId',
])

const customerOptionsWithSearchLabel = computed(() => {
  return props.customerOptions.map((customer) => {
    const name = String(customer?.name ?? '').trim()
    const phone = String(customer?.phone ?? '').trim()

    return {
      ...customer,
      search_label: phone !== '' ? `${name} · ${phone}` : name,
    }
  })
})

const selectedCustomer = computed(() => {
  return props.customerOptions.find((customer) => customer.id === props.selectedCustomerId) ?? null
})

const selectedCustomerIdModel = computed({
  get() {
    return props.selectedCustomerId === '' ? null : props.selectedCustomerId
  },
  set(value) {
    emit('update:selectedCustomerId', value ?? '')
  },
})

const showClearFiltersAction = computed(() => {
  return props.selectedCategoryId !== '' || props.productSearchQuery.trim() !== ''
})

const isMobileCartOpen = ref(false)

function openMobileCart() {
  isMobileCartOpen.value = true
}

function closeMobileCart() {
  isMobileCartOpen.value = false
}

function updateSelectedCategoryId(event) {
  emit('update:selectedCategoryId', event.target.value)
}

function updateProductSearchQuery(event) {
  emit('update:productSearchQuery', event.target.value)
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

function isLowStock(product) {
  return Number(product?.is_low_stock ?? 0) === 1
}

function formatPrice(price) {
  return formatMoneyByBookSettings(price, props.book)
}

function formatQuantity(quantity) {
  const formattedQuantity = Number(quantity ?? 0).toFixed(3)

  return formattedQuantity.replace(/\.?0+$/, '')
}
</script>
