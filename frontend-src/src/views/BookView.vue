<template>

    <div v-if="errorMessage" class="alert" :class="errorMessage === bookNotFoundMessage ? 'alert-warning' : 'alert-danger'" role="alert">
      {{ errorMessage }}
    </div>

    <div v-else-if="isLoadingBook && !book" class="text-secondary">{{ $t('bookView.loadingBook') }}</div>

    <template v-else-if="book">
      <component :is="activeComponent" v-if="activeComponent" :key="book.id" :book="book" />

      <div v-else class="alert alert-info" role="alert">
        {{ $t('bookView.unsupportedType') }}
      </div>
    </template>

    <div v-else class="alert alert-warning" role="alert">
      {{ BOOK_NOT_FOUND_MESSAGE }}
    </div>
    
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { isNotFoundError, isUnauthorizedError } from '@/api/errors'
import { fetchBookById } from '@/api/books-api'
import { useBooksStore } from '@/stores/books-store'
import FinanceApp from '@/views/book-types/finance/FinanceApp.vue'
import MinishopApp from '@/views/book-types/minishop/MinishopApp.vue'
import NotesApp from '@/views/book-types/notes/NotesApp.vue'
import TodoApp from '@/views/book-types/todo/TodoApp.vue'

// Reuse one message constant so the template and loader logic stay in sync.
const { t } = useI18n()
const bookNotFoundMessage = computed(() => t('bookView.notFound'))

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

// Prefer sidebar metadata immediately, and only keep a local fallback when the
// shared books list truly cannot resolve a direct URL.
const fallbackBook = ref(null)
const isLoadingBook = ref(true)
const errorMessage = ref('')
const bookId = computed(() => String(route.params.bookId ?? ''))
const storeBook = computed(() => booksStore.findBookById(bookId.value))
const book = computed(() => storeBook.value ?? fallbackBook.value)

// Once we know the book type, choose the matching child app component.
const activeComponent = computed(() => componentByType[book.value?.type_key] ?? null)

async function resolveFallbackBook(currentBookId) {
  try {
    // Single-book metadata is a defensive-only fallback after the shared list
    // path cannot resolve the route cleanly.
    const { data } = await fetchBookById(currentBookId)
    fallbackBook.value = data.book ?? null

    if (!book.value) {
      errorMessage.value = bookNotFoundMessage.value
    }
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    if (isNotFoundError(error)) {
      errorMessage.value = bookNotFoundMessage.value
      return
    }

    errorMessage.value = t('bookView.unableLoad')
  }
}

onMounted(async () => {
  // BookView is keyed by bookId in AppLayout, so onMounted runs again for each
  // selected book and we do not need a watch() or stale-request bookkeeping.
  const currentBookId = bookId.value

  if (!currentBookId) {
    errorMessage.value = bookNotFoundMessage.value
    isLoadingBook.value = false
    return
  }

  errorMessage.value = ''
  fallbackBook.value = null

  // Sidebar navigation should render book metadata immediately from the shared
  // books store without waiting for any async work.
  if (storeBook.value) {
    isLoadingBook.value = false
    return
  }

  try {
    try {
      // If this page was opened directly, wait for the shared list load once and
      // then try the local lookup again.
      await booksStore.fetchBooks()

      if (storeBook.value) {
        return
      }
    } catch (error) {
      if (isUnauthorizedError(error)) {
        router.replace({ name: 'login' })
        return
      }
      // Any non-auth list failure still falls through to the single-book
      // defensive lookup before surfacing a generic book-load error.
    }

    if (!storeBook.value) {
      await resolveFallbackBook(currentBookId)
    }
  } finally {
    isLoadingBook.value = false
  }
})
</script>
