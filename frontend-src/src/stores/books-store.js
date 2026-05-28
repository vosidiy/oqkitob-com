import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { i18n } from '@/i18n'
import { fetchArchivedBooksList, fetchBooksList } from '@/api/books-api'

// Keep the shared list request outside the store state so we can dedupe calls
// without polluting serializable Pinia state.
let listPromise = null
let archivedListPromise = null

export const useBooksStore = defineStore('books', () => {
  // Shared book metadata cache used by the sidebar and by BookView lookups.
  const books = ref([])
  const archivedBooks = ref([])

  // List-level UI state used by the dashboard shell.
  const isLoading = ref(false)
  const isLoadingArchived = ref(false)
  const loaded = ref(false)
  const loadedArchived = ref(false)
  const errorMessage = ref('')
  const archivedErrorMessage = ref('')
  const hasArchivedBooks = computed(() => archivedBooks.value.length > 0)

  async function fetchBooks(force = false) {
    // Reuse the active list request unless the caller explicitly asks for a refresh.
    if (listPromise && !force) {
      return listPromise
    }

    // Once loaded, serve the cached list by default.
    if (loaded.value && !force) {
      return books.value
    }

    isLoading.value = true
    errorMessage.value = ''

    // If a refresh fails, keep showing the last known good list instead of blanking the UI.
    const existingBooks = books.value

    listPromise = (async () => {
      try {
        const { data } = await fetchBooksList()

        // The list endpoint is the main frontend source for book metadata such
        // as title, type_key, and description.
        books.value = data.books ?? []
        loaded.value = true

        return books.value
      } catch (error) {
        errorMessage.value = i18n.global.t('appLayout.unableDashboard')
        books.value = existingBooks
        throw error
      } finally {
        // Clear the shared promise when the request completes so future calls can refetch if needed.
        isLoading.value = false
        listPromise = null
      }
    })()

    return listPromise
  }

  async function fetchArchivedBooks(force = false) {
    if (archivedListPromise && !force) {
      return archivedListPromise
    }

    if (loadedArchived.value && !force) {
      return archivedBooks.value
    }

    isLoadingArchived.value = true
    archivedErrorMessage.value = ''

    const existingArchivedBooks = archivedBooks.value

    archivedListPromise = (async () => {
      try {
        const { data } = await fetchArchivedBooksList()
        archivedBooks.value = data.books ?? []
        loadedArchived.value = true
        return archivedBooks.value
      } catch (error) {
        archivedErrorMessage.value = i18n.global.t('appLayout.unableLoadArchivedBooks')
        archivedBooks.value = existingArchivedBooks
        throw error
      } finally {
        isLoadingArchived.value = false
        archivedListPromise = null
      }
    })()

    return archivedListPromise
  }

  function findBookById(bookId) {
    // Book detail views use the warmed sidebar metadata first and only fall
    // back to the single-book endpoint when the shared list truly cannot help.
    return books.value.find((candidateBook) => candidateBook.id === bookId) ?? null
  }

  function reset() {
    // Clear both UI state and the shared list request marker on logout.
    books.value = []
    archivedBooks.value = []
    isLoading.value = false
    isLoadingArchived.value = false
    loaded.value = false
    loadedArchived.value = false
    errorMessage.value = ''
    archivedErrorMessage.value = ''
    listPromise = null
    archivedListPromise = null
  }

  return {
    books,
    archivedBooks,
    isLoading,
    isLoadingArchived,
    loaded,
    loadedArchived,
    errorMessage,
    archivedErrorMessage,
    hasArchivedBooks,
    fetchBooks,
    fetchArchivedBooks,
    findBookById,
    reset,
  }
})
