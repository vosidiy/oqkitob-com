<template>
  <div class="d-flex flex-col h-full w-full overflow-hidden bg-secondary">
    <template v-if="isBookListRoute">
      <header class="d-flex align-items-center gap-2 h-14 flex-shrink-0 bg-base border-bottom border-color-neutral-300 px-3">
        <a href="/home" class="hover:opacity-80 d-flex text-decoration-none align-items-center m-0 mr-auto">
          <img src="/assets/img/logo.svg" alt="" height="28">
          <div style="font-size:20px;" class="font-semibold ml-1">Oq<span class="text-secondary">kitob</span></div>
        </a>

        <select id="mobile_language_picker" v-model="currentLocale" class="form-select max-w-20">
          <option v-for="option in localeOptions" :key="option.code" :value="option.code">
            {{ option.label }}
          </option>
        </select>
      </header>

      <main class="flex-grow overflow-y-auto scrollbar-thin p-3">
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <div class="d-flex align-items-center mb-3">
          <div>
            <h1 class="text-xl">{{ $t('nav.home') }}</h1>
            <p class="text-secondary text-sm">{{ user?.name || user?.email || '-' }}</p>
          </div>
        </div>

        <div v-if="booksStore.isLoading" class="text-secondary">{{ $t('appLayout.loadingBooks') }}</div>

        <div v-else-if="booksStore.books.length === 0" class="text-secondary">
          {{ $t('appLayout.noBooks') }}
        </div>

        <div v-else>
          <RouterLink
            v-for="book in booksStore.books"
            :key="book.id"
            :to="{ name: 'book-detail', params: { bookId: book.id } }"
            class="card border-transparent bg-raised hover:border-color-neutral-400 d-flex flex-row align-items-center relative p-3 mb-2"
          >
            <div class="w-12 h-12 d-flex flex-center rounded overflow-hidden flex-shrink-0 bg-neutral-0">
              <img src="/assets/img/book-finance.png" width="42" alt="Book">
            </div>
            <div class="p-2 min-w-0">
              <h2 class="text-base text-capitalize font-semibold">{{ book.title }}</h2>
              <div
                v-if="book.description"
                class="text-secondary text-sm mt-1"
                style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"
              >
                {{ book.description }}
              </div>
              <p class="text-secondary text-sm mt-1">{{ $t('bookTypes.' + book.type_key) }}</p>
            </div>
          </RouterLink>
        </div>
      </main>

      <footer class="d-flex gap-2 flex-shrink-0 bg-base border-top border-color-neutral-300 p-3">
        <a href="https://t.me/websoft1990" target="_blank" class="btn btn-plain border text-secondary flex-grow">
          {{ $t('appLayout.help') }}
        </a>
        <button type="button" class="btn btn-plain border text-secondary flex-grow" @click="handleLogout" :disabled="isLoggingOut">
          <span v-if="isLoggingOut">{{ $t('common.states.loggingOut') }}</span>
          <span v-else>{{ $t('common.actions.logout') }}</span>
        </button>
      </footer>
    </template>

    <RouterView v-else :key="routerViewKey" />
  </div>
</template>

<script setup>
import { computed, onMounted, provide, ref } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { isUnauthorizedError } from '@/api/errors'
import { useLocale } from '@/composables/use-locale'
import { authStore } from '@/stores/auth'
import { useBooksStore } from '@/stores/books-store'

const router = useRouter()
const route = useRoute()
const booksStore = useBooksStore()
const { t } = useI18n()
const { currentLocale, localeOptions } = useLocale()

const errorMessage = ref('')
const isLoggingOut = ref(false)
const user = computed(() => authStore.state.user)
const isBookListRoute = computed(() => route.name === 'dashboard-home')

const routerViewKey = computed(() => {
  if (route.name === 'book-detail') {
    return String(route.params.bookId ?? 'book-detail')
  }

  return String(route.name ?? route.path)
})

provide('isMobileAppLayout', true)
provide('openBookSettingsDialog', () => {})

onMounted(async () => {
  try {
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

    errorMessage.value = booksStore.errorMessage || t('appLayout.unableDashboard')
  }
})

async function handleLogout() {
  isLoggingOut.value = true

  try {
    await authStore.logout()
    booksStore.reset()
    window.location.replace('/')
  } catch {
    errorMessage.value = t('appLayout.unableLogout')
  } finally {
    isLoggingOut.value = false
  }
}
</script>
