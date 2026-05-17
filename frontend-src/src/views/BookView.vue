<template>
  <div class="d-flex flex-col h-full w-full">
     <header class="d-flex h-14 mobile:h-auto mobile:py-2 shadow-sm border-bottom border-color-neutral-300 flex-shrink-0 bg-base px-5 align-items-center gap-1">
      <div class="min-w-50 mobile:min-w-auto mobile:flex-grow mobile:mb-2">
        <h5 class="text-xl text-capitalize">
          {{ book?.title || (isLoadingBook ? 'Loading book...' : 'Book') }}
        </h5>
        <p class="text-secondary text-sm">
          <template v-if="book">
            {{ book.type_key }} book
            <span v-if="book.description"> | {{ book.description }}</span>
          </template>
          <template v-else-if="isLoadingBook">
            Please wait while the selected book is loading.
          </template>
          <template v-else>
            Book details are unavailable.
          </template>
        </p>
      </div>
      <nav class="nav-tabs font-medium mx-auto mobile:order-4 mobile:w-full mobile:flex-nowrap">
        <a class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0 active" href="#"> Page entry  </a>
        <a class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0" href="#"> Page second </a>
        <a class="tab-link py-4 mobile:py-2 mobile:px-1 rounded-0" href="#"> Page other </a>
      </nav>
      <div class="d-flex justify-content-end gap-1 min-w-50 mobile:min-w-auto mobile:mb-2">
        <div class="relative">
          <button type="button" class="btn btn-default px-2 gap-1">
            Setting &#9662;
          </button>
          <nav class="dropdown right-0" style="display: none;">
            <button class="dropdown-item">Book settings</button>
            <button type="button" class="dropdown-item text-red">Delete book</button>
          </nav>
        </div>
      </div>
    </header>


    <div v-if="errorMessage" class="alert" :class="errorMessage === BOOK_NOT_FOUND_MESSAGE ? 'alert-warning' : 'alert-danger'" role="alert">
      {{ errorMessage }}
    </div>

    <div v-else-if="isLoadingBook && !book" class="text-secondary">Loading book...</div>

    <template v-else-if="book">
      <component :is="activeComponent" v-if="activeComponent" :key="book.id" :book="book" />

      <div v-else class="alert alert-info" role="alert">
        This book type is not supported yet.
      </div>
    </template>

    <div v-else class="alert alert-warning" role="alert">
      {{ BOOK_NOT_FOUND_MESSAGE }}
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
  }
}

onMounted(async () => {
  // BookView is keyed by bookId in AppLayout, so onMounted runs again for each
  // selected book and we do not need a watch() or stale-request bookkeeping.
  const currentBookId = bookId.value

  if (!currentBookId) {
    errorMessage.value = BOOK_NOT_FOUND_MESSAGE
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
