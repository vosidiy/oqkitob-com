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
              <h2 class="h5 mb-3">Books</h2>

              <div v-if="isLoadingBooks" class="text-secondary">Loading books...</div>

              <div v-else-if="books.length === 0" class="text-secondary">
                No books available yet.
              </div>

              <div v-else class="list-group">
                <div
                  v-for="book in books"
                  :key="book.id"
                  class="list-group-item list-group-item-action"
                >
                  <div class="fw-semibold">{{ book.title }}</div>
                  <div class="small text-secondary text-capitalize">{{ book.type_key }}</div>
                  <div v-if="book.description" class="small text-secondary mt-1">{{ book.description }}</div>
                </div>
              </div>
            </div>
          </div>
        </aside>

        <section class="col-lg-8 col-xl-9">
          <div class="card mb-4">
            <div class="card-body">
              <h2 class="h5 mb-3">User Information</h2>
              <div v-if="user" class="row g-3">
                <div class="col-md-6">
                  <div class="text-secondary small">Name</div>
                  <div class="fw-semibold">{{ user.name || '-' }}</div>
                </div>
                <div class="col-md-6">
                  <div class="text-secondary small">Email</div>
                  <div class="fw-semibold">{{ user.email || '-' }}</div>
                </div>
                <div class="col-md-6">
                  <div class="text-secondary small">City</div>
                  <div class="fw-semibold">{{ user.city || '-' }}</div>
                </div>
                <div class="col-md-6">
                  <div class="text-secondary small">Country</div>
                  <div class="fw-semibold">{{ user.country_name || '-' }}</div>
                </div>
                <div class="col-md-6">
                  <div class="text-secondary small">Timezone</div>
                  <div class="fw-semibold">{{ user.timezone || '-' }}</div>
                </div>
                <div class="col-md-6">
                  <div class="text-secondary small">Plan</div>
                  <div class="fw-semibold text-capitalize">{{ user.plan || '-' }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body py-5">
              <h2 class="h5 mb-2">Main Content</h2>
              <p class="text-secondary mb-0">This area is intentionally blank for the MVP and will hold book content later.</p>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup>
import axios from 'axios'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '@/services/api'
import { authStore } from '@/stores/auth'

const router = useRouter()

const user = ref(authStore.state.user)
const books = ref([])
const errorMessage = ref('')
const isLoadingBooks = ref(true)
const isLoggingOut = ref(false)

onMounted(async () => {
  try {
    const authenticatedUser = await authStore.ensureChecked()

    if (!authenticatedUser) {
      router.replace('/')

      return
    }

    user.value = authenticatedUser

    const { data } = await api.get('/books')
    books.value = data.books ?? []
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      router.replace('/')

      return
    }

    errorMessage.value = 'Unable to load dashboard data right now.'
  } finally {
    isLoadingBooks.value = false
  }
})

async function handleLogout() {
  isLoggingOut.value = true

  try {
    await authStore.logout()
    router.replace('/')
  } catch (error) {
    errorMessage.value = 'Unable to log out right now.'
  } finally {
    isLoggingOut.value = false
  }
}
</script>
