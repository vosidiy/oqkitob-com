<template>
  <div class="card">
    <div class="card-body">
      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <template v-else-if="book">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
          <div>
            <h2 class="h5 mb-1">{{ book.title }}</h2>
            <div class="small text-secondary text-capitalize">{{ book.type_key }} book</div>
          </div>
        </div>

        <div v-if="book.description" class="text-secondary mb-3">{{ book.description }}</div>

        <div v-if="isLoading" class="text-secondary">Loading content...</div>

        <template v-else>
          <component :is="activeComponent" v-if="activeComponent" :items="items" />

          <div v-else class="alert alert-secondary mb-0" role="alert">
            This book type is not supported yet.
          </div>
        </template>
      </template>

      <div v-else class="alert alert-warning mb-0" role="alert">
        Book not found.
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FinanceBookContent from '@/components/books/FinanceBookContent.vue'
import NotesBookContent from '@/components/books/NotesBookContent.vue'
import TodoBookContent from '@/components/books/TodoBookContent.vue'
import { api } from '@/services/api'
import { booksStore } from '@/stores/books'

const componentByType = {
  finance: FinanceBookContent,
  notes: NotesBookContent,
  todo: TodoBookContent,
}

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const errorMessage = ref('')
const items = ref([])

const bookId = computed(() => String(route.params.bookId ?? ''))
const book = computed(() => booksStore.findById(bookId.value))

// This view owns route-driven content loading and then hands rendering off to
// the matching presentational book component.
const activeComponent = computed(() => {
  if (!book.value) {
    return null
  }

  return componentByType[book.value.type_key] ?? null
})

watch(
  () => route.params.bookId,
  async () => {
    await ensureBooksLoaded()
    await loadContent()
  },
  { immediate: true }
)

async function ensureBooksLoaded() {
  if (!booksStore.state.loaded) {
    try {
      await booksStore.fetchBooks()
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        router.replace('/')
      }
    }
  }
}

async function loadContent() {
  errorMessage.value = ''
  items.value = []

  if (!book.value) {
    errorMessage.value = 'Book not found.'
    return
  }

  const endpointByType = {
    finance: `/books/${book.value.id}/finance`,
    notes: `/books/${book.value.id}/notes`,
    todo: `/books/${book.value.id}/todos`,
  }

  const endpoint = endpointByType[book.value.type_key]

  if (!endpoint) {
    return
  }

  isLoading.value = true

  try {
    const { data } = await api.get(endpoint)

    if (book.value.type_key === 'notes') {
      items.value = data.notes ?? []
    } else if (book.value.type_key === 'todo') {
      items.value = data.todos ?? []
    } else if (book.value.type_key === 'finance') {
      items.value = data.transactions ?? []
    }
  } catch (error) {
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 401) {
        router.replace('/')
        return
      }

      if (error.response?.status === 404) {
        errorMessage.value = 'Book not found.'
        return
      }
    }

    errorMessage.value = 'Unable to load book content right now.'
  } finally {
    isLoading.value = false
  }
}
</script>
