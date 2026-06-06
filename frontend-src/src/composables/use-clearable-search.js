import { computed, onBeforeUnmount, ref } from 'vue'

const DEFAULT_SEARCH_DELAY = 500

export function hasSearchValue(value) {
  return String(value ?? '').trim() !== ''
}

export function useClearableSearch(options = {}) {
  const {
    delay = DEFAULT_SEARCH_DELAY,
    initialValue = '',
    onSearch = null,
  } = options

  const searchQuery = ref(String(initialValue ?? ''))
  const hasActiveSearch = computed(() => hasSearchValue(searchQuery.value))
  let searchTimer = null

  function cancelPendingSearch() {
    if (searchTimer !== null) {
      window.clearTimeout(searchTimer)
      searchTimer = null
    }
  }

  function runSearch(value = searchQuery.value) {
    if (typeof onSearch === 'function') {
      onSearch(String(value ?? '').trim())
    }
  }

  function handleSearchInput() {
    cancelPendingSearch()

    searchTimer = window.setTimeout(() => {
      searchTimer = null
      runSearch(searchQuery.value)
    }, delay)
  }

  function clearSearch() {
    cancelPendingSearch()
    searchQuery.value = ''
    runSearch('')
  }

  onBeforeUnmount(() => {
    cancelPendingSearch()
  })

  return {
    cancelPendingSearch,
    clearSearch,
    handleSearchInput,
    hasActiveSearch,
    searchQuery,
  }
}
