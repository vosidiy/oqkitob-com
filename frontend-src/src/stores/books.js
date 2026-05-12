import { reactive } from 'vue'
import { api } from '@/services/api'

const state = reactive({
  books: [],
  isLoading: false,
  loaded: false,
  errorMessage: '',
  fetchPromise: null,
})

export const booksStore = {
  state,

  async fetchBooks(force = false) {
    // Shell and child views can ask for books at the same time; this keeps
    // them on one in-flight request and reuses loaded state afterwards.
    if (state.fetchPromise) {
      return state.fetchPromise
    }

    if (state.loaded && !force) {
      return state.books
    }

    state.isLoading = true
    state.errorMessage = ''

    state.fetchPromise = (async () => {
      try {
        const { data } = await api.get('/books')
        state.books = data.books ?? []
        state.loaded = true

        return state.books
      } catch (error) {
        state.errorMessage = 'Unable to load books right now.'
        state.books = []
        throw error
      } finally {
        state.isLoading = false
        state.fetchPromise = null
      }
    })()

    return state.fetchPromise
  },

  findById(bookId) {
    return state.books.find((book) => book.id === bookId) ?? null
  },

  reset() {
    state.books = []
    state.isLoading = false
    state.loaded = false
    state.errorMessage = ''
    state.fetchPromise = null
  },
}
