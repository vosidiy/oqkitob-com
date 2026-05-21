<template>
  <div class="d-flex flex-col h-full w-full">
    <BookPageHeader :book="book" />

    <div class="p-5">
      <h3 class="h6 mb-3">{{ $t('finance.title') }}</h3>

      <div v-if="isLoading" class="text-secondary">{{ $t('finance.loading') }}</div>

      <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <div v-else-if="transactions.length === 0" class="text-secondary">{{ $t('finance.empty') }}</div>

      <div v-else class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead>
            <tr>
              <th scope="col">{{ $t('common.fields.date') }}</th>
              <th scope="col">{{ $t('common.fields.type') }}</th>
              <th scope="col">{{ $t('common.fields.amount') }}</th>
              <th scope="col">{{ $t('common.fields.note') }}</th>
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
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { isUnauthorizedError } from '@/api/errors'
import { fetchFinanceTransactions } from '@/api/finance'
import BookPageHeader from '@/components/BookPageHeader.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const transactions = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const hasStartedLoadingTransactions = ref(false)

watch(() => route.params.page, async (page) => {
  if (page) {
    await router.replace({
      name: 'book-detail',
      params: {
        bookId: props.book.id,
      },
    })
    return
  }

  if (hasStartedLoadingTransactions.value) {
    return
  }

  hasStartedLoadingTransactions.value = true
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

    errorMessage.value = t('finance.unableLoad')
  } finally {
    isLoading.value = false
  }
}, { immediate: true })
</script>
