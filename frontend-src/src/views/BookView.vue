<template>
  <div class="card">
    <div class="card-body">
      <div
        v-if="errorMessage"
        class="alert mb-0"
        :class="errorMessage === BOOK_NOT_FOUND_MESSAGE ? 'alert-warning' : 'alert-danger'"
        role="alert"
      >
        {{ errorMessage }}
      </div>

      <div v-else-if="isLoadingBook && !book" class="text-secondary">Loading book...</div>

      <template v-else-if="book">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
          <div>
            <h2 class="h5 mb-1">{{ book.title }}</h2>
            <div class="small text-secondary text-capitalize">{{ book.type_key }} book</div>
          </div>
        </div>

        <div v-if="book.description" class="text-secondary mb-3">{{ book.description }}</div>

        <component :is="activeComponent" v-if="activeComponent" :key="book.id" :book="book" />

        <div v-else class="alert alert-secondary mb-0" role="alert">
          This book type is not supported yet.
        </div>
      </template>

      <div v-else class="alert alert-warning mb-0" role="alert">
        {{ BOOK_NOT_FOUND_MESSAGE }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { isNotFoundError, isUnauthorizedError } from '@/api/errors'
import { fetchBookById } from '@/api/books-api'
import { useBooksStore } from '@/stores/books-store'
import FinanceApp from '@/views/book-types/finance/FinanceApp.vue'
import MinishopApp from '@/views/book-types/minishop/MinishopApp.vue'
import NotesApp from '@/views/book-types/notes/NotesApp.vue'
import TodoApp from '@/views/book-types/todo/TodoApp.vue'

// Reuse one message constant so the template and loader logic stay in sync.
const BOOK_NOT_FOUND_MESSAGE = 'Book not found.'

// BookView only chooses the correct mini app. Each child app owns its own data fetch.
const componentByType = {
  finance: FinanceApp,
  minishop: MinishopApp,
  notes: NotesApp,
  todo: TodoApp,
}

// Route params tell us which book should be visible, and the router lets us redirect on auth failures.
const route = useRoute()
const router = useRouter()

// The books store still owns the shared list used by the sidebar and by the
// first-pass selected-book lookup.
const booksStore = useBooksStore()

// BookView keeps the selected book locally because each book page now remounts
// as its own fresh mini app when the route's bookId changes.
const book = ref(null)
const isLoadingBook = ref(true)
const errorMessage = ref('')

// Once we know the book type, choose the matching child app component.
const activeComponent = computed(() => componentByType[book.value?.type_key] ?? null)

onMounted(async () => {
  // BookView is keyed by bookId in AppLayout, so onMounted runs again for each
  // selected book and we do not need a watch() or stale-request bookkeeping.
  const currentBookId = String(route.params.bookId ?? '')

  if (!currentBookId) {
    errorMessage.value = BOOK_NOT_FOUND_MESSAGE
    isLoadingBook.value = false
    return
  }

  errorMessage.value = ''

  try {
    // First reuse any metadata already present from the shared sidebar book list.
    const cachedBook = booksStore.findBookById(currentBookId)

    if (cachedBook) {
      book.value = cachedBook
      return
    }

    // If this page was opened directly, wait for the shared list load once and
    // then try the local lookup again.
    await booksStore.fetchBooks()

    const bookFromList = booksStore.findBookById(currentBookId)

    if (bookFromList) {
      book.value = bookFromList
      return
    }

    // Final fallback only: ask the backend for one book when the warmed list
    // still cannot resolve the page, such as a direct URL or stale local data.
    const { data } = await fetchBookById(currentBookId)
    book.value = data.book ?? null

    if (!book.value) {
      errorMessage.value = BOOK_NOT_FOUND_MESSAGE
    }
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      errorMessage.value = BOOK_NOT_FOUND_MESSAGE
      return
    }

    errorMessage.value = 'Unable to load book right now.'
  } finally {
    isLoadingBook.value = false
  }
})
</script>
