<template>
  <div class="d-flex flex-col h-full w-full mobile:pt-25">
    <BookPageHeader :book="book">
      <template #nav>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'orders' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'orders' }"
        >
          {{ $t('service.tabs.orders') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'clients' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'clients' }"
        >
          {{ $t('service.tabs.clients') }}
        </RouterLink>
        <RouterLink
          :to="{ name: 'book-detail', params: { bookId: book.id, page: 'reports' } }"
          class="tab-link py-4 mobile:py-2 mobile:px-1 flex-grow justify-content-center rounded-0"
          :class="{ active: activePageKey === 'reports' }"
        >
          {{ $t('service.tabs.reports') }}
        </RouterLink>
      </template>
    </BookPageHeader>

    <OrdersTab v-if="activePageKey === 'orders'" :book="book" />
    <ClientsTab v-else-if="activePageKey === 'clients'" :book="book" />
    <ReportsTab v-else :book="book" />
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import BookPageHeader from '@/components/BookPageHeader.vue'
import ClientsTab from '@/views/book-types/service/ClientsTab.vue'
import OrdersTab from '@/views/book-types/service/OrdersTab.vue'
import ReportsTab from '@/views/book-types/service/ReportsTab.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const route = useRoute()
const router = useRouter()
const allowedPageKeys = new Set(['orders', 'clients', 'reports'])
const normalizedPageParam = computed(() => String(route.params.page ?? '').trim())
const activePageKey = computed(() => normalizedPageParam.value === '' ? 'orders' : normalizedPageParam.value)

watch(normalizedPageParam, async (page) => {
  if (page !== '' && !allowedPageKeys.has(page)) {
    await router.replace({
      name: 'book-detail',
      params: {
        bookId: props.book.id,
        page: 'orders',
      },
    })
  }
}, { immediate: true })
</script>
