<template>
  <div class="min-vh-100 bg-light">
    <header class="border-bottom bg-white">
      <div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3">
        <div>
          <h1 class="h3 mb-1">Dashboard</h1>
          <p class="text-secondary mb-0">Your books and account overview.</p>
        </div>
        <button type="button" class="btn btn-outline-dark" @click="handleLogout" :disabled="isLoggingOut">
          <span v-if="isLoggingOut">Logging out...</span>
          <span v-else>Logout</span>
        </button>
      </div>
    </header>

    <main class="container py-4">
      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <div class="row g-4">
        <aside class="col-lg-4 col-xl-3">
          <div class="card h-100">
            <div class="card-body">
              <div class="border rounded p-3 mb-3 bg-light">
                <div class="small text-secondary">Signed in as</div>
                <div class="fw-semibold">{{ user?.name || '-' }}</div>
                <div class="small text-secondary">{{ user?.email || '-' }}</div>
              </div>

              <h2 class="h5 mb-3">Books</h2>

              <div v-if="booksStore.isLoading" class="text-secondary">Loading books...</div>

              <div v-else-if="booksStore.books.length === 0" class="text-secondary">
                No books available yet.
              </div>

              <div v-else class="list-group">
                <RouterLink
                  v-for="book in booksStore.books"
                  :key="book.id"
                  :to="{ name: 'book-detail', params: { bookId: book.id } }"
                  class="list-group-item list-group-item-action"
                  :class="{ active: selectedBookId === book.id }"
                >
                  <div class="fw-semibold">{{ book.title }}</div>
                  <div class="small text-capitalize" :class="selectedBookId === book.id ? 'text-white-50' : 'text-secondary'">
                    {{ book.type_key }}
                  </div>
                  <div
                    v-if="book.description"
                    class="small mt-1"
                    :class="selectedBookId === book.id ? 'text-white-50' : 'text-secondary'"
                  >
                    {{ book.description }}
                  </div>
                </RouterLink>
              </div>

              <div class="border-top mt-3 pt-3">
                <button type="button" class="btn btn-dark w-100" @click="openCreateBookDialog">
                  Create new book
                </button>
              </div>
            </div>
          </div>
        </aside>

        <section class="col-lg-8 col-xl-9">
          <RouterView :key="routerViewKey" />
        </section>
      </div>
    </main>

    <dialog
      ref="createBookDialog"
      class="border rounded shadow p-0"
      @cancel="handleCreateBookDialogCancel"
      @close="handleCreateBookDialogClose"
    >
      <form class="m-0" @submit.prevent="handleCreateBook">
        <div class="border-bottom px-4 py-3">
          <h2 class="h5 mb-1">Create new book</h2>
          <p class="text-secondary mb-0">Choose a name and book type for your new mini app.</p>
        </div>

        <div class="px-4 py-3">
          <div v-if="createBookErrorMessage" class="alert alert-danger" role="alert">
            {{ createBookErrorMessage }}
          </div>

          <div class="mb-3">
            <label class="form-label" for="create-book-title">Name</label>
            <input
              id="create-book-title"
              v-model.trim="createBookForm.title"
              type="text"
              class="form-control"
              placeholder="Enter book name"
              :disabled="isCreatingBook"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label" for="create-book-description">Description</label>
            <textarea
              id="create-book-description"
              v-model.trim="createBookForm.description"
              class="form-control"
              rows="3"
              placeholder="Add a short description"
              :disabled="isCreatingBook"
            ></textarea>
          </div>

          <fieldset class="mb-0">
            <legend class="form-label mb-2">Book type</legend>

            <div v-if="isLoadingBookTypes" class="text-secondary">Loading book types...</div>

            <div v-else-if="bookTypes.length === 0" class="text-secondary">
              No book types are available right now.
            </div>

            <div v-else class="vstack gap-2">
              <div
                v-for="bookType in bookTypes"
                :key="bookType.type_key"
                class="form-check border rounded p-3"
              >
                <input
                  :id="`book-type-${bookType.type_key}`"
                  v-model="createBookForm.type_key"
                  class="form-check-input"
                  type="radio"
                  name="book-type"
                  :value="bookType.type_key"
                  :disabled="isCreatingBook"
                >
                <label class="form-check-label w-100" :for="`book-type-${bookType.type_key}`">
                  <div class="fw-semibold">{{ bookType.name }}</div>
                  <div v-if="bookType.description" class="small text-secondary mt-1">
                    {{ bookType.description }}
                  </div>
                </label>
              </div>
            </div>
          </fieldset>
        </div>

        <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" @click="closeCreateBookDialog" :disabled="isCreatingBook">
            Cancel
          </button>
          <button type="submit" class="btn btn-dark" :disabled="isCreateBookSubmitDisabled">
            <span v-if="isCreatingBook">Creating...</span>
            <span v-else>Create</span>
          </button>
        </div>
      </form>
    </dialog>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { createBookRequest, fetchBookTypes } from '@/api/books-api'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import { authStore } from '@/stores/auth'
import { useBooksStore } from '@/stores/books-store'

const router = useRouter()
const route = useRoute()
const booksStore = useBooksStore()

const errorMessage = ref('')
const isLoggingOut = ref(false)
const createBookDialog = ref(null)
const bookTypes = ref([])
const hasLoadedBookTypes = ref(false)
const isLoadingBookTypes = ref(false)
const isCreatingBook = ref(false)
const createBookErrorMessage = ref('')

const createBookForm = reactive({
  title: '',
  description: '',
  type_key: '',
})

const user = computed(() => authStore.state.user)
const selectedBookId = computed(() => String(route.params.bookId ?? ''))
const isCreateBookSubmitDisabled = computed(() => {
  return (
    isLoadingBookTypes.value ||
    isCreatingBook.value ||
    bookTypes.value.length === 0 ||
    createBookForm.title === '' ||
    createBookForm.type_key === ''
  )
})

// Remount the book detail page whenever the route's bookId changes so each
// book mini app starts with fresh local state, dialogs, and filters.
const routerViewKey = computed(() => {
  if (route.name === 'book-detail') {
    return String(route.params.bookId ?? 'book-detail')
  }

  return String(route.name ?? route.path)
})

onMounted(async () => {
  try {
    // The shell is the single place that warms the shared sidebar book list.
    // Child views can still call fetchBooks(), but the store dedupes the request.
    const authenticatedUser = await authStore.ensureChecked()

    if (!authenticatedUser) {
      router.replace({ name: 'login' })
      return
    }

    await booksStore.fetchBooks()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = booksStore.errorMessage || 'Unable to load dashboard data right now.'
  }
})

async function handleLogout() {
  isLoggingOut.value = true

  try {
    // Logout also clears the shared books cache so the next session starts clean.
    await authStore.logout()
    booksStore.reset()
    router.replace({ name: 'login' })
  } catch {
    errorMessage.value = 'Unable to log out right now.'
  } finally {
    isLoggingOut.value = false
  }
}

async function openCreateBookDialog() {
  createBookErrorMessage.value = ''

  if (!createBookDialog.value?.open) {
    createBookDialog.value?.showModal()
  }

  if (!hasLoadedBookTypes.value && !isLoadingBookTypes.value) {
    await loadBookTypes()
  }
}

function closeCreateBookDialog() {
  if (createBookDialog.value?.open) {
    createBookDialog.value.close()
  }
}

function handleCreateBookDialogClose() {
  resetCreateBookForm()
}

function handleCreateBookDialogCancel(event) {
  if (isCreatingBook.value) {
    event.preventDefault()
  }
}

async function loadBookTypes() {
  isLoadingBookTypes.value = true
  createBookErrorMessage.value = ''

  try {
    const { data } = await fetchBookTypes()
    bookTypes.value = data.bookTypes ?? []
    hasLoadedBookTypes.value = true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateBookDialog()
      router.replace({ name: 'login' })
      return
    }

    createBookErrorMessage.value = getApiErrorMessage(error, 'Unable to load book types right now.')
  } finally {
    isLoadingBookTypes.value = false
  }
}

async function handleCreateBook() {
  if (isCreateBookSubmitDisabled.value) {
    return
  }

  createBookErrorMessage.value = ''
  isCreatingBook.value = true
  let responseData = null

  try {
    const { data } = await createBookRequest({
      title: createBookForm.title,
      description: createBookForm.description,
      type_key: createBookForm.type_key,
    })
    responseData = data
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateBookDialog()
      router.replace({ name: 'login' })
      return
    }

    createBookErrorMessage.value = getApiErrorMessage(error, 'Unable to create book right now.')
    return
  } finally {
    isCreatingBook.value = false
  }

  const createdBookId = typeof responseData?.book?.id === 'string'
    ? responseData.book.id.trim()
    : ''

  if (createdBookId === '') {
    createBookErrorMessage.value = 'The server response was missing the new book ID. Please refresh and try again.'
    return
  }

  // Use a full page navigation here to keep the create flow simple and rely
  // on the existing dashboard bootstrap to reload the sidebar and selected book.
  const targetUrl = router.resolve({
    name: 'book-detail',
    params: {
      bookId: createdBookId,
    },
  }).href

  closeCreateBookDialog()
  window.location.assign(targetUrl)
}

function resetCreateBookForm() {
  createBookForm.title = ''
  createBookForm.description = ''
  createBookForm.type_key = ''
  createBookErrorMessage.value = ''
  isCreatingBook.value = false
}
</script>
