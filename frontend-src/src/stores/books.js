import { ref } from 'vue'
import { defineStore } from 'pinia'
import { fetchBooksList } from '@/api/books'

// Keep the shared list request outside the store state so we can dedupe calls
// without polluting serializable Pinia state.
let listPromise = null

export const useBooksStore = defineStore('books', () => {
  // Shared book metadata cache used by the sidebar and by BookView lookups.
  const books = ref([])

  // List-level UI state used by the dashboard shell.
  const isLoading = ref(false)
  const loaded = ref(false)
  const errorMessage = ref('')

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
        errorMessage.value = 'Unable to load books right now.'
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

  function reset() {
    // Clear both UI state and the shared list request marker on logout.
    books.value = []
    isLoading.value = false
    loaded.value = false
    errorMessage.value = ''
    listPromise = null
  }

  return {
    books,
    isLoading,
    loaded,
    errorMessage,
    fetchBooks,
    reset,
  }
})
