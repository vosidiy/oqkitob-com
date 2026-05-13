<template>
  <div>
    <h3 class="h6 mb-3">Transactions</h3>

    <div v-if="isLoading" class="text-secondary">Loading transactions...</div>

    <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <div v-else-if="transactions.length === 0" class="text-secondary">No transactions yet.</div>

    <div v-else class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">Date</th>
            <th scope="col">Type</th>
            <th scope="col">Amount</th>
            <th scope="col">Note</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in transactions" :key="item.id">
            <td>{{ item.transaction_date }}</td>
            <td class="text-capitalize">{{ item.type }}</td>
            <td>{{ item.amount }} {{ item.currency_code }}</td>
            <td>{{ item.note || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { isUnauthorizedError } from '@/api/errors'
import { fetchFinanceTransactions } from '@/api/finance'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const transactions = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const { data } = await fetchFinanceTransactions(props.book.id)
    transactions.value = data.transactions ?? []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = 'Unable to load transactions.'
  } finally {
    isLoading.value = false
  }
})
</script>
