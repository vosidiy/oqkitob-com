<template>
  <section class="row">
    <aside class="col-6">
      <div class="flex-1 overflow-y-auto">
        <div v-if="salesListErrorMessage" class="alert alert-danger m-4" role="alert">
          {{ salesListErrorMessage }}
        </div>

        <div v-else-if="isLoadingSalesList" class="px-4 py-4 text-secondary">
          Loading sales...
        </div>

        <div v-else-if="sales.length === 0" class="px-4 py-4 text-secondary">
          No sales yet.
        </div>

        <ul v-else class="mb-0">
          <li v-for="sale in sales" :key="sale.id" class="border-bottom">
            <button
              type="button"
              class="w-full text-left px-4 py-3 bg-transparent"
              :class="{ 'bg-neutral-100': selectedSaleId === sale.id }"
              @click="selectSale(sale)"
            >
              <div class="d-flex justify-content-between gap-2 mb-1">
                <strong class="text-sm">{{ sale.id }}</strong>
                <span class="text-sm">{{ formatMoney(sale.total_amount) }}</span>
              </div>

              <div class="d-flex justify-content-between gap-2 text-sm text-secondary">
                <span>{{ formatDateTime(sale.sold_at) }}</span>
                <span class="text-capitalize">{{ sale.payment_status }}</span>
              </div>

              <p v-if="Number(sale.due_amount) > 0" class="text-orange text-sm mt-1 mb-0">
                Due: {{ formatMoney(sale.due_amount) }}
              </p>
            </button>
          </li>
        </ul>
      </div>
    </aside>
    <aside class="col-6">
    <section class="flex-1 overflow-y-auto p-5">
      <div v-if="selectedSaleErrorMessage" class="alert alert-danger mb-4" role="alert">
        {{ selectedSaleErrorMessage }}
      </div>

      <div v-if="isLoadingSelectedSale" class="card">
        <div class="card-body text-secondary">Loading receipt...</div>
      </div>

      <div v-else-if="selectedSale" class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start gap-3 mb-4 mobile:flex-col">
            <div>
              <h3 class="h5 mb-1">Receipt</h3>
              <p class="text-secondary mb-0">{{ selectedSale.id }}</p>
            </div>

            <div class="text-right mobile:text-left">
              <p class="mb-1"><strong>Sold at:</strong> {{ formatDateTime(selectedSale.sold_at) }}</p>
              <p class="mb-1"><strong>Currency:</strong> {{ selectedSale.currency_code }}</p>
              <p class="mb-0 text-capitalize"><strong>Status:</strong> {{ selectedSale.payment_status }}</p>
            </div>
          </div>

          <div class="table-responsive mb-4">
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
                <tr v-for="item in selectedSaleItems" :key="item.id">
                  <td>{{ item.product_name }}</td>
                  <td>{{ formatQuantity(item.quantity) }}</td>
                  <td>{{ formatMoney(item.unit_price) }}</td>
                  <td>{{ formatMoney(item.line_total) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-flex flex-col gap-2">
            <div class="d-flex justify-content-between gap-3">
              <span>Subtotal</span>
              <strong>{{ formatMoney(selectedSale.subtotal_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Discount</span>
              <strong>- {{ formatMoney(selectedSale.discount_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Total</span>
              <strong>{{ formatMoney(selectedSale.total_amount) }}</strong>
            </div>
            <div class="d-flex justify-content-between gap-3">
              <span>Paid</span>
              <strong>{{ formatMoney(selectedSale.paid_amount) }}</strong>
            </div>
            <div
              v-if="Number(selectedSale.due_amount) > 0"
              class="d-flex justify-content-between gap-3 text-orange"
            >
              <span>Remaining debt</span>
              <strong>{{ formatMoney(selectedSale.due_amount) }}</strong>
            </div>
            <div v-else class="d-flex justify-content-between gap-3 text-green">
              <span>Status</span>
              <strong>Paid in full</strong>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="card">
        <div class="card-body text-center py-6">
          <h3 class="h6 mb-2">Select a sale</h3>
          <p class="text-secondary mb-0">
            Choose any sale from the list to view its receipt details here.
          </p>
        </div>
      </div>
    </section>
    </aside>
  </section>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { getApiErrorMessage, isNotFoundError, isUnauthorizedError } from '@/api/errors'
import { fetchMinishopSale, fetchMinishopSales } from '@/api/minishop'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const sales = ref([])
const selectedSaleId = ref('')
const selectedSale = ref(null)
const selectedSaleItems = ref([])
const isLoadingSalesList = ref(false)
const isLoadingSelectedSale = ref(false)
const salesListErrorMessage = ref('')
const selectedSaleErrorMessage = ref('')

watch(() => props.book.id, async () => {
  await loadSales()
}, { immediate: true })

async function loadSales() {
  isLoadingSalesList.value = true
  salesListErrorMessage.value = ''
  selectedSaleId.value = ''
  selectedSale.value = null
  selectedSaleItems.value = []
  selectedSaleErrorMessage.value = ''

  try {
    const { data } = await fetchMinishopSales(props.book.id)
    sales.value = Array.isArray(data.sales) ? data.sales : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    sales.value = []
    salesListErrorMessage.value = getApiErrorMessage(error, 'Unable to load sales right now.')
  } finally {
    isLoadingSalesList.value = false
  }
}

async function selectSale(sale) {
  selectedSaleId.value = sale.id
  selectedSaleErrorMessage.value = ''
  isLoadingSelectedSale.value = true

  try {
    const { data } = await fetchMinishopSale(props.book.id, sale.id)
    selectedSale.value = data.sale ?? null
    selectedSaleItems.value = Array.isArray(data.items) ? data.items : []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      await router.replace({ name: 'login' })
      return
    }

    selectedSale.value = null
    selectedSaleItems.value = []
    selectedSaleErrorMessage.value = isNotFoundError(error)
      ? 'This sale is no longer available.'
      : getApiErrorMessage(error, 'Unable to load this receipt right now.')
  } finally {
    isLoadingSelectedSale.value = false
  }
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
