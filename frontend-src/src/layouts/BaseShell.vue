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

              <div v-if="booksState.isLoading" class="text-secondary">Loading books...</div>

              <div v-else-if="books.length === 0" class="text-secondary">
                No books available yet.
              </div>

              <div v-else class="list-group">
                <RouterLink
                  v-for="book in books"
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
            </div>
          </div>
        </aside>

        <section class="col-lg-8 col-xl-9">
          <RouterView />
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import { authStore } from '@/stores/auth'
import { booksStore } from '@/stores/books'

const router = useRouter()
const route = useRoute()

const errorMessage = ref('')
const isLoggingOut = ref(false)

const user = computed(() => authStore.state.user)
const books = computed(() => booksStore.state.books)
const booksState = booksStore.state
const selectedBookId = computed(() => String(route.params.bookId ?? ''))

onMounted(async () => {
  try {
    // The shell owns the shared authenticated layout, so it loads sidebar
    // books once and child views reuse the same store state.
    const authenticatedUser = await authStore.ensureChecked()

    if (!authenticatedUser) {
      router.replace('/')
      return
    }

    await booksStore.fetchBooks()
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      router.replace('/')
      return
    }

    errorMessage.value = booksStore.state.errorMessage || 'Unable to load dashboard data right now.'
  }
})

async function handleLogout() {
  isLoggingOut.value = true

  try {
    await authStore.logout()
    booksStore.reset()
    router.replace('/')
  } catch (error) {
    errorMessage.value = 'Unable to log out right now.'
  } finally {
    isLoggingOut.value = false
  }
}
</script>
