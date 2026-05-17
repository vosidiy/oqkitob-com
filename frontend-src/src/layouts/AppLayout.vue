<template>
  
  <aside class="col-3 h-full min-w-80 mobile:max-w-full max-w-100 d-flex  flex-col  flex-shrink-0  bg-secondary border-right mobile:col-12">
   
    <section class="w-full border-bottom h-14 flex-shrink-0">
      <div class="d-flex p-1 px-2 align-items-center flex-grow gap-4 flex-row">
        <nav class="flex-grow">
          <div class="small text-secondary">Signed in as</div>
          <div class="font-semibold">{{ user?.name || '-' }}</div>
          <div class="small text-secondary">{{ user?.email || '-' }}</div>
        </nav>
        <button onclick="themeSwitcher()" class="btn btn-icon btn-plain mr-3 text-secondary">
            <span class="d-none dark:d-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="align-middle" fill="none" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
            <span class="dark:d-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="align-middle" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </span>
        </button>
      </div>
    </section>
 
    <section class="overflow-y-auto scrollbar-thin flex-grow p-4">

        <div v-if="booksStore.isLoading" class="text-secondary">Loading books...</div>

        <div v-else-if="booksStore.books.length === 0" class="text-secondary">
          No books available yet.
        </div>

        <div v-else class="mb-4">
          <RouterLink
            v-for="book in booksStore.books"
            :key="book.id"
            :to="{ name: 'book-detail', params: { bookId: book.id } }"
            class="card group border-transparent bg-raised hover:border-color-neutral-400 group d-flex flex-row relative p-3 mb-2 mobile:py-1"
            :class="{ active: selectedBookId === book.id }"
          >
            <div class="w-18 rounded overflow-hidden mobile:w-12" style="max-height:105px;">
                <img src="/assets/img/book-finance-open.png" width="80" alt="Book" class="d-none group-hover:d-block mobile:h-12 mobile:w-10">
                <img src="/assets/img/book-finance.png" width="80" alt="Book" class="d-block group-hover:d-none mobile:h-12 mobile:w-10">
            </div>
            <div class="p-3 mobile:p-1" data-book-id="{{ book.id }}">
                <h6 class="mb-1 mobile:mb-0 text-capitalize">  {{ book.title }}  </h6>
                <p class="text-secondary">  {{ book.type_key }} </p>
                <div
                  v-if="book.description"
                  class="small mt-1"
                  :class="selectedBookId === book.id ? 'text-white-50' : 'text-secondary'"
                >
                  {{ book.description }}
                </div>
            </div>
            
          </RouterLink>
        </div>
        <button class="btn btn-default w-full mt-2" @click="openCreateBookDialog"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text-icon lucide-book-text"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path><path d="M8 11h8"></path><path d="M8 7h6"></path></svg> Create book </button>

    
    </section>
    <section class="mt-auto border-top border-color-neutral-300 p-4 mobile:d-none">
      <!-- sidebar-bottom -->
      <div class="d-flex justify-content-between">
            <a href="#" class="hover:opacity-80 d-flex text-decoration-none align-items-center m-0">
                <img src="/assets/img/logo.svg" alt="" height="32">
                <div style="font-size:22px;" class="font-semibold ml-1">Oq<span class="text-secondary">kitob</span> </div>
            </a>
            <div class="d-flex flex-nowrap">
                <a href="https://t.me/websoft1990" target="_blank" class="btn mr-1 text-secondary btn-plain border">
                    Yordam
                </a>
                <button type="button" class="btn btn-outline" @click="handleLogout" :disabled="isLoggingOut">
                  <span v-if="isLoggingOut">Logging out...</span>
                  <span v-else>Logout</span>
                </button>
            </div>
      </div>
      <!-- sidebar-bottom end.// -->
    </section>
  </aside>

  <div class="flex-grow h-full">
    <div v-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>
      
    <RouterView :key="routerViewKey" />
  </div>

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
          <button type="button" class="btn btn-outline" @click="closeCreateBookDialog" :disabled="isCreatingBook">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isCreateBookSubmitDisabled">
            <span v-if="isCreatingBook">Creating...</span>
            <span v-else>Create</span>
          </button>
        </div>
      </form>
  </dialog>

  <dialog
    ref="bookSettingsDialog"
    class="border rounded shadow p-0"
    @cancel="handleBookSettingsDialogCancel"
    @close="handleBookSettingsDialogClose"
  >
    <div class="border-bottom px-4 py-3">
      <h2 class="h5 mb-1">Book settings</h2>
      <p class="text-secondary mb-0">
        Settings for "{{ activeBookForSettings?.title || 'this book' }}" will live here.
      </p>
    </div>

    <div class="px-4 py-3">
      <div v-if="bookSettingsErrorMessage" class="alert alert-danger mb-3" role="alert">
        {{ bookSettingsErrorMessage }}
      </div>

      <p class="text-secondary mb-3">Choose a book-level action.</p>

      <div class="d-flex gap-2 flex-wrap">
        <button
          type="button"
          class="btn btn-outline"
          :disabled="!hasActiveBookForSettings || isBookSettingsActionPending"
          @click="handleArchiveBook"
        >
          <span v-if="activeBookSettingsAction === 'archive'">Archiving...</span>
          <span v-else>Archive book</span>
        </button>

        <button
          type="button"
          class="btn btn-outline text-red"
          :disabled="!hasActiveBookForSettings || isBookSettingsActionPending"
          @click="handleDeleteBook"
        >
          <span v-if="activeBookSettingsAction === 'delete'">Deleting...</span>
          <span v-else>Delete book</span>
        </button>
      </div>
    </div>

    <div class="border-top px-4 py-3 d-flex justify-content-end">
      <button
        type="button"
        class="btn btn-outline"
        :disabled="isBookSettingsActionPending"
        @click="closeBookSettingsDialog"
      >
        Close
      </button>
    </div>
  </dialog>

</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import {
  archiveBookRequest,
  createBookRequest,
  deleteBookRequest,
  fetchBookTypes,
} from '@/api/books-api'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import { useBookSettingsDialog } from '@/composables/book-settings-dialog'
import { authStore } from '@/stores/auth'
import { useBooksStore } from '@/stores/books-store'

const router = useRouter()
const route = useRoute()
const booksStore = useBooksStore()

const errorMessage = ref('')
const isLoggingOut = ref(false)
const createBookDialog = ref(null)
const bookSettingsDialog = ref(null)
const bookTypes = ref([])
const hasLoadedBookTypes = ref(false)
const isLoadingBookTypes = ref(false)
const isCreatingBook = ref(false)
const createBookErrorMessage = ref('')
const bookSettingsErrorMessage = ref('')
const activeBookSettingsAction = ref('')
const {
  activeBook: activeBookForSettings,
  isOpen: isBookSettingsDialogOpen,
  closeBookSettingsDialog,
  clearBookSettingsDialog,
} = useBookSettingsDialog()

const createBookForm = reactive({
  title: '',
  description: '',
  type_key: '',
})

const user = computed(() => authStore.state.user)
const selectedBookId = computed(() => String(route.params.bookId ?? ''))
const hasActiveBookForSettings = computed(() => {
  return typeof activeBookForSettings.value?.id === 'string' && activeBookForSettings.value.id.trim() !== ''
})
const isBookSettingsActionPending = computed(() => activeBookSettingsAction.value !== '')
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

watch(isBookSettingsDialogOpen, (isOpen) => {
  const dialog = bookSettingsDialog.value

  if (!dialog) {
    return
  }

  if (isOpen) {
    if (!dialog.open) {
      dialog.showModal()
    }
    return
  }

  if (dialog.open) {
    dialog.close()
  }
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

function handleBookSettingsDialogCancel(event) {
  event.preventDefault()

  if (isBookSettingsActionPending.value) {
    return
  }

  closeBookSettingsDialog()
}

function handleBookSettingsDialogClose() {
  resetBookSettingsDialogState()
  clearBookSettingsDialog()
}

function resetBookSettingsDialogState() {
  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = ''
}

async function handleArchiveBook() {
  const bookId = String(activeBookForSettings.value?.id ?? '').trim()

  if (bookId === '' || isBookSettingsActionPending.value) {
    return
  }

  if (!window.confirm('Archive this book?')) {
    return
  }

  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = 'archive'

  try {
    await archiveBookRequest(bookId)
    closeBookSettingsDialog()
    await booksStore.fetchBooks(true)
    await router.replace({ name: 'dashboard-home' })
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeBookSettingsDialog()
      router.replace({ name: 'login' })
      return
    }

    bookSettingsErrorMessage.value = getApiErrorMessage(error, 'Unable to archive book right now.')
  } finally {
    activeBookSettingsAction.value = ''
  }
}

async function handleDeleteBook() {
  const bookId = String(activeBookForSettings.value?.id ?? '').trim()

  if (bookId === '' || isBookSettingsActionPending.value) {
    return
  }

  if (!window.confirm('Delete this book?')) {
    return
  }

  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = 'delete'

  try {
    await deleteBookRequest(bookId)
    closeBookSettingsDialog()
    await booksStore.fetchBooks(true)
    await router.replace({ name: 'dashboard-home' })
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeBookSettingsDialog()
      router.replace({ name: 'login' })
      return
    }

    bookSettingsErrorMessage.value = getApiErrorMessage(error, 'Unable to delete book right now.')
  } finally {
    activeBookSettingsAction.value = ''
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
