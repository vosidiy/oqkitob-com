<template>
<div class="d-flex h-full overflow-hidden mobile:flex-col">
  
  <aside class="col-3 h-full min-w-80 mobile:max-w-full max-w-100 d-flex  flex-col  flex-shrink-0  bg-secondary border-right border-width-2 border-color-neutral-400 mobile:col-12">
   
    <section class="w-full border-bottom border-color-neutral-300 h-14 flex-shrink-0">
      <div class="d-flex p-1 px-2 align-items-center flex-grow gap-4 flex-row">
        <a
          href="#"
          id="btn_dialog_profile"
          role="button"
          class="rounded flex-grow align-items-center gap-2 text-base hover:bg-neutral-200 d-flex min-w-60% h-auto p-1 text-left"
          @click.prevent="openProfileDialog"
        >
          <span class="w-8 shadow-sm h-8 d-flex flex-center overflow-hidden flex-shrink-0 flex-grow-0 bg-neutral-0 rounded">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"></circle><path d="M20 21a8 8 0 0 0-16 0"></path></svg>
          </span>
          <div class="ml-1 lh-sm text-base w-100%">
            <p class="font-semibold">{{ user?.name || '-' }}</p>
            <small class="text-sm text-secondary">{{ user?.email || '-' }}</small>
          </div>
        </a>
        <select id="language_picker" v-model="currentLocale" class="form-select max-w-20">
          <option v-for="option in localeOptions" :key="option.code" :value="option.code">
            {{ option.label }}
          </option>
        </select>
      </div>
    </section>
 
    <section class="overflow-y-auto scrollbar-thin flex-grow p-4">

        <div v-if="booksStore.isLoading" class="text-secondary">{{ $t('appLayout.loadingBooks') }}</div>

        <div v-else-if="booksStore.books.length === 0" class="text-secondary">
          {{ $t('appLayout.noBooks') }}
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
                <h6 class="text-capitalize">  {{ book.title }}  </h6>
                <div class="text-capitalize" v-if="book.description"
                  :class="selectedBookId === book.id ? 'text-base' : 'text-secondary'"
                >
                  {{ book.description }}
                </div>
                <p class="text-secondary mt-1">{{ $t('bookTypes.' + book.type_key) }}</p>
            </div>
            
          </RouterLink>
        </div>
        <button class="btn btn-default w-full mt-2" @click="openCreateBookDialog"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text-icon lucide-book-text"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path><path d="M8 11h8"></path><path d="M8 7h6"></path></svg> {{ $t('appLayout.createBook') }} </button>

    
    </section>
    <section class="mt-auto border-top border-color-neutral-300 p-4 mobile:d-none">
      <!-- sidebar-bottom -->
      <div class="d-flex justify-content-between">
            <a href="/home" class="hover:opacity-80 d-flex text-decoration-none align-items-center m-0">
                <img src="/assets/img/logo.svg" alt="" height="32">
                <div style="font-size:22px;" class="font-semibold ml-1">Oq<span class="text-secondary">kitob</span> </div>
            </a>
            <div class="d-flex flex-nowrap">
                <a href="https://t.me/websoft1990" target="_blank" class="btn mr-1 text-secondary btn-plain border">
                    {{ $t('appLayout.help') }}
                </a>
                <button type="button" class="btn  btn-plain border text-secondary" @click="handleLogout" :disabled="isLoggingOut">
                  <span v-if="isLoggingOut">{{ $t('common.states.loggingOut') }}</span>
                  <span v-else>{{ $t('common.actions.logout') }}</span>
                </button>
            </div>
      </div>
      <!-- sidebar-bottom end.// -->
    </section>
  </aside>

  <div class="flex-grow h-full">
    <!-- main content -->
    <div v-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>
      
    <RouterView :key="routerViewKey" />

    <!-- main content end.// -->
  </div>

</div>

  <dialog
    ref="profileDialog"
    class="dialog-md"
    @cancel="handleProfileDialogCancel"
    @close="handleProfileDialogClose"
  >
    <header class="dialog-header">
      <div class="d-flex align-items-center gap-2">
        <b class="avatar bg-primary-300 text-white avatar-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </b>
        <h5>{{ $t('profileDialog.title') }}</h5>
      </div>
      <button type="button" class="btn btn-icon" :disabled="isProfileActionPending" @click="closeProfileDialog">
        <svg viewBox="0 0 24 24" width="24" height="24">
          <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
        </svg>
      </button>
    </header>

    <div class="dialog-body">
      <article class="d-flex gap-2 py-4 pt-0 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12">{{ $t('profileDialog.account') }}</label>
        <div class="col-8 mobile:col-12">
          <p class="text-secondary mb-1">
            {{ $t('profileDialog.accountCreatedAt') }}: {{ formattedProfileCreatedAt }}
          </p>
          <b>{{ user?.email || '-' }}</b>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12" for="profile-name">{{ $t('profileDialog.name') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex gap-2 justify-content-between mobile:flex-col">
            <input
              id="profile-name"
              v-model.trim="profileForm.name"
              type="text"
              class="form-control"
              :placeholder="$t('profileDialog.namePlaceholder')"
              :disabled="isProfileActionPending"
            >
            <button
              type="button"
              class="btn btn-primary flex-shrink-0"
              :disabled="isNameSaveDisabled"
              @click="handleProfileNameSave"
            >
              <span v-if="isSavingProfileName">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.save') }}</span>
            </button>
          </div>

          <div v-if="profileErrorMessages.name" class="alert alert-danger mt-2 mb-0" role="alert">
            {{ profileErrorMessages.name }}
          </div>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12" for="profile-phone">{{ $t('profileDialog.phone') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex gap-2 justify-content-between mobile:flex-col">
            <input
              id="profile-phone"
              v-model.trim="profileForm.phone"
              type="text"
              class="form-control"
              :placeholder="$t('profileDialog.phonePlaceholder')"
              :disabled="isProfileActionPending"
            >
            <button
              type="button"
              class="btn btn-primary flex-shrink-0"
              :disabled="isPhoneSaveDisabled"
              @click="handleProfilePhoneSave"
            >
              <span v-if="isSavingProfilePhone">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.save') }}</span>
            </button>
          </div>

          <div v-if="profileErrorMessages.phone" class="alert alert-danger mt-2 mb-0" role="alert">
            {{ profileErrorMessages.phone }}
          </div>
        </div>
      </article>

      <article class="d-flex gap-2 py-4 align-items-stretch border-bottom mobile:flex-col">
        <label class="col-4 mobile:col-12">{{ $t('profileDialog.password') }}</label>
        <div class="col-8 mobile:col-12">
          <div class="d-flex justify-content-between align-items-center gap-2 mobile:flex-col mobile:align-items-stretch">
            <span>{{ $t('profileDialog.passwordMask') }}</span>
            <button
              v-if="!showPasswordUpdateForm"
              type="button"
              class="btn btn-default"
              :disabled="isProfileActionPending"
              @click="openPasswordUpdateForm"
            >
              {{ $t('profileDialog.changePassword') }}
            </button>
          </div>

          <form v-if="showPasswordUpdateForm" class="mt-3" @submit.prevent="handleProfilePasswordSave">
            <div class="mb-4">
              <label class="form-label" for="profile-current-password">{{ $t('profileDialog.currentPassword') }}</label>
              <input
                id="profile-current-password"
                v-model="passwordForm.current_password"
                type="password"
                class="form-control"
                :placeholder="$t('profileDialog.currentPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="current-password"
              >
            </div>

            <div class="mb-2">
              <label class="form-label" for="profile-new-password">{{ $t('profileDialog.newPassword') }}</label>
              <input
                id="profile-new-password"
                v-model="passwordForm.new_password"
                type="password"
                class="form-control mb-2"
                :placeholder="$t('profileDialog.newPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="new-password"
              >
              <input
                id="profile-new-password-confirmation"
                v-model="passwordForm.new_password_confirmation"
                type="password"
                class="form-control mb-2"
                :placeholder="$t('profileDialog.repeatNewPasswordPlaceholder')"
                :disabled="isProfileActionPending"
                autocomplete="new-password"
              >
            </div>

            <div v-if="profileErrorMessages.password" class="alert alert-danger mb-3" role="alert">
              {{ profileErrorMessages.password }}
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary" :disabled="isPasswordSaveDisabled">
                <span v-if="isSavingProfilePassword">{{ $t('common.states.saving') }}</span>
                <span v-else>{{ $t('profileDialog.saveNewPassword') }}</span>
              </button>
              <button
                type="button"
                class="btn btn-default"
                :disabled="isProfileActionPending"
                @click="closePasswordUpdateForm"
              >
                {{ $t('common.actions.cancel') }}
              </button>
            </div>
          </form>
        </div>
      </article>

      <div class="d-flex gap-2 mt-4 justify-content-between mobile:flex-col">
        <button
          type="button"
          class="btn btn-plain border text-red"
          :disabled="isLoggingOut || isProfileActionPending"
          @click="handleLogout"
        >
          <span v-if="isLoggingOut">{{ $t('common.states.loggingOut') }}</span>
          <span v-else>{{ $t('common.actions.logout') }}</span>
        </button>
        <button type="button" class="btn btn-default" :disabled="isProfileActionPending" @click="closeProfileDialog">
          {{ $t('common.actions.close') }}
        </button>
      </div>
    </div>
  </dialog>

  <dialog
      ref="createBookDialog"
      class="mt-10"
      @cancel="handleCreateBookDialogCancel"
      @close="handleCreateBookDialogClose"
    >
      
      <div class="dialog-body">
        <button class="btn-close-dialog">
          <svg viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
            <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
          </svg> 
        </button>

        <header>
          <h4 class="mb-1">{{ $t('appLayout.createBookDialogTitle') }}</h4>
        </header>

        <hr>

        <div v-if="createBookErrorMessage" class="alert alert-danger" role="alert">
            {{ createBookErrorMessage }}
        </div>

        <form @submit.prevent="handleCreateBook">

          <fieldset class="mb-4">
            <legend class="form-label mb-2">{{ $t('appLayout.chooseBookType') }}</legend>
            <div v-if="isLoadingBookTypes" class="text-secondary">{{ $t('appLayout.loadingBookTypes') }}</div>
            <div v-else-if="bookTypes.length === 0" class="text-secondary">
              {{ $t('appLayout.noBookTypes') }}
            </div>

            <div v-else class="gap-2">
              <div class="mb-2" v-for="bookType in bookTypes" :key="bookType.type_key">
                <label class="card-check w-full d-block p-3 rounded" :for="`book-type-${bookType.type_key}`">
                  <input
                    :id="`book-type-${bookType.type_key}`"
                    v-model="createBookForm.type_key"
                    class="form-check-input float-right"
                    type="radio"
                    name="book-type"
                    :value="bookType.type_key"
                    :disabled="isCreatingBook"
                    @change="handleCreateBookTypeChange(bookType)"
                  >
                  <p class="font-semibold">  📙 {{ $t('bookTypes.' + bookType.type_key) }}</p>
                  <p v-if="bookType.description" class="small text-secondary mt-1">  {{ bookType.description }}  </p>
                </label>
              </div>
            </div>
          </fieldset>
          
          <div v-if="showCreateBookFields" class="mt-3">
            <div class="mb-2">
              <label class="form-label" for="create-book-title">{{ $t('common.fields.name') }}</label>
              <input
                id="create-book-title"
                v-model.trim="createBookForm.title"
                type="text"
                class="form-control text-capitalize"
                :placeholder="$t('appLayout.enterBookName')"
                :disabled="isCreatingBook"
                required
              >
            </div>

            <div class="mb-4">
              <textarea
                id="create-book-description"
                v-model.trim="createBookForm.description"
                class="form-control"
                rows="2"
                :placeholder="$t('appLayout.addBookDescription')"
                :disabled="isCreatingBook"
              ></textarea>
            </div>

            <div class="mb-4" v-if="showCreateBookCurrencyField">
              <label class="form-label" for="create-book-currency">{{ $t('common.fields.currency') }}</label>
              <select
                id="create-book-currency"
                v-model="createBookForm.currency_code"
                class="form-select  max-w-50"
                :disabled="isCreatingBook"
              >
                <option value="" disabled>{{ $t('appLayout.selectCurrency') }}</option>
                <option
                  v-for="currencyOption in BOOK_CURRENCY_OPTIONS"
                  :key="currencyOption.code"
                  :value="currencyOption.code"
                >
                  {{ currencyOption.label }}
                </option>
              </select>
              <div class="small text-secondary mt-1">{{ $t('appLayout.bookCurrencyLockedHint') }}</div>
            </div>
          </div>

          <footer class="border-top pt-4 d-flex gap-2">
            <button type="submit" class="btn flex-1 btn-primary" :disabled="isCreateBookSubmitDisabled">
              <span v-if="isCreatingBook">{{ $t('common.states.creating') }}</span>
              <span v-else>{{ $t('common.actions.create') }}</span>
            </button>
            <button type="button" class="btn flex-1 btn-default" @click="closeCreateBookDialog" :disabled="isCreatingBook">
              {{ $t('common.actions.cancel') }}
            </button>
          </footer>
        </form>
        
      </div>  <!-- dialog-body .//end -->
      
  </dialog>

  <dialog
    ref="bookSettingsDialog"
    class="border rounded shadow p-0"
    @cancel="handleBookSettingsDialogCancel"
    @close="handleBookSettingsDialogClose"
  >
    <div class="border-bottom px-4 py-3">
      <h2 class="h5 mb-1">{{ $t('appLayout.bookSettingsTitle') }}</h2>
      <p class="text-secondary mb-0">
        {{ $t('appLayout.bookSettingsDescription', { title: bookForSettings?.title || $t('appLayout.thisBook') }) }}
      </p>
    </div>

    <div class="px-4 py-3">
      <div v-if="bookSettingsErrorMessage" class="alert alert-danger mb-3" role="alert">
        {{ bookSettingsErrorMessage }}
      </div>

      <div class="mb-3">
        <label class="form-label" for="book-settings-title">{{ $t('common.fields.name') }}</label>
        <input
          id="book-settings-title"
          v-model.trim="bookSettingsForm.title"
          type="text"
          class="form-control"
          :placeholder="$t('appLayout.enterBookName')"
          :disabled="isBookSettingsActionPending"
        >
      </div>

      <div class="mb-3">
        <label class="form-label" for="book-settings-description">{{ $t('common.fields.description') }}</label>
        <textarea
          id="book-settings-description"
          v-model.trim="bookSettingsForm.description"
          class="form-control"
          rows="3"
          :placeholder="$t('appLayout.addBookDescription')"
          :disabled="isBookSettingsActionPending"
        ></textarea>
      </div>

      <div class="d-flex gap-3 mobile:flex-col">
        <div class="flex-grow">
          <label class="form-label">{{ $t('common.fields.type') }}</label>
          <p class="mb-0">
            {{ bookForSettings?.type_key ? $t('bookTypes.' + bookForSettings.type_key) : '-' }}
          </p>
        </div>

        <div v-if="bookForSettings?.currency_code" class="flex-grow">
          <label class="form-label">{{ $t('common.fields.currency') }}</label>
          <p class="mb-0">{{ formatCurrencyDisplay(bookForSettings?.currency_code) }}</p>
        </div>
      </div>

      <p class="small text-secondary mt-2 mb-4">{{ $t('appLayout.bookCurrencyLockedHint') }}</p>

      <p class="text-secondary mb-3">{{ $t('appLayout.bookActionPrompt') }}</p>

      <div class="d-flex gap-2 flex-wrap">
        <button
          type="button"
          class="btn btn-outline"
          :disabled="!bookForSettings?.id || isBookSettingsActionPending"
          @click="handleArchiveBook"
        >
          <span v-if="activeBookSettingsAction === 'archive'">{{ $t('common.states.archiving') }}</span>
          <span v-else>{{ $t('appLayout.archiveBook') }}</span>
        </button>

        <button
          type="button"
          class="btn btn-outline text-red"
          :disabled="!bookForSettings?.id || isBookSettingsActionPending"
          @click="handleDeleteBook"
        >
          <span v-if="activeBookSettingsAction === 'delete'">{{ $t('common.states.deleting') }}</span>
          <span v-else>{{ $t('appLayout.deleteBook') }}</span>
        </button>
      </div>
    </div>

    <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
      <button
        type="button"
        class="btn btn-outline"
        :disabled="isBookSettingsActionPending"
        @click="closeBookSettingsDialog"
      >
        {{ $t('common.actions.close') }}
      </button>
      <button
        type="button"
        class="btn btn-primary"
        :disabled="!bookForSettings?.id || isBookSettingsActionPending || bookSettingsForm.title.trim() === ''"
        @click="handleUpdateBookSettings"
      >
        <span v-if="isSavingBookSettings">{{ $t('common.states.saving') }}</span>
        <span v-else>{{ $t('common.actions.saveChanges') }}</span>
      </button>
    </div>
  </dialog>

</template>

<script setup>
import { computed, onMounted, provide, reactive, ref } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import {
  archiveBookRequest,
  createBookRequest,
  deleteBookRequest,
  fetchBookTypes,
  updateBookRequest,
} from '@/api/books-api'
import { updatePasswordRequest, updateProfileRequest } from '@/api/auth'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import { useI18n } from 'vue-i18n'
import { useLocale } from '@/composables/use-locale'
import { authStore } from '@/stores/auth'
import { useBooksStore } from '@/stores/books-store'

const BOOK_CURRENCY_OPTIONS = [
  { code: 'EUR', label: '🇪🇺 EUR (€) - Euro' },
  { code: 'RUB', label: '🇷🇺 RUB (₽) - Russian ruble' },
  { code: 'UZS', label: '🇺🇿 UZS - Uzbekistan som' },
  { code: 'USD', label: '🇺🇸 USD ($) - US Dollar' },
]

const router = useRouter()
const route = useRoute()
const booksStore = useBooksStore()
const { t } = useI18n()
const { currentLocale, localeOptions } = useLocale()

const errorMessage = ref('')
const isLoggingOut = ref(false)
const profileDialog = ref(null)
const createBookDialog = ref(null)
const bookSettingsDialog = ref(null)
const bookTypes = ref([])
const hasLoadedBookTypes = ref(false)
const isLoadingBookTypes = ref(false)
const isCreatingBook = ref(false)
const createBookErrorMessage = ref('')
const bookSettingsErrorMessage = ref('')
const activeBookSettingsAction = ref('')
const activeProfileAction = ref('')
const showPasswordUpdateForm = ref(false)
const showCreateBookFields = ref(false)
const showCreateBookCurrencyField = ref(false)
const bookForSettings = ref(null)

const profileForm = reactive({
  name: '',
  phone: '',
})
const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})
const createBookForm = reactive({
  title: '',
  description: '',
  type_key: '',
  currency_code: '',
})
const bookSettingsForm = reactive({
  title: '',
  description: '',
})
const profileErrorMessages = reactive({
  name: '',
  phone: '',
  password: '',
})

const user = computed(() => authStore.state.user)
const selectedBookId = computed(() => String(route.params.bookId ?? ''))
const currentUserName = computed(() => String(user.value?.name ?? '').trim())
const currentUserPhone = computed(() => String(user.value?.phone ?? '').trim())
const isBookSettingsActionPending = computed(() => activeBookSettingsAction.value !== '')
const isSavingBookSettings = computed(() => activeBookSettingsAction.value === 'save')
const isProfileActionPending = computed(() => activeProfileAction.value !== '')
const isSavingProfileName = computed(() => activeProfileAction.value === 'name')
const isSavingProfilePhone = computed(() => activeProfileAction.value === 'phone')
const isSavingProfilePassword = computed(() => activeProfileAction.value === 'password')
const isNameSaveDisabled = computed(() => {
  return (
    isProfileActionPending.value ||
    profileForm.name.trim() === '' ||
    profileForm.name.trim() === currentUserName.value
  )
})
const isPhoneSaveDisabled = computed(() => {
  return isProfileActionPending.value || profileForm.phone.trim() === currentUserPhone.value
})
const isPasswordSaveDisabled = computed(() => {
  return (
    isProfileActionPending.value ||
    passwordForm.current_password === '' ||
    passwordForm.new_password === '' ||
    passwordForm.new_password_confirmation === ''
  )
})
const isCreateBookSubmitDisabled = computed(() => {
  return (
    isLoadingBookTypes.value ||
    isCreatingBook.value ||
    bookTypes.value.length === 0 ||
    createBookForm.type_key === '' ||
    !showCreateBookFields.value ||
    createBookForm.title.trim() === '' ||
    (showCreateBookCurrencyField.value && createBookForm.currency_code === '')
  )
})
const formattedProfileCreatedAt = computed(() => {
  const rawValue = String(user.value?.created_at ?? '').trim()

  if (rawValue === '') {
    return '-'
  }

  const parsedDate = new Date(rawValue.replace(' ', 'T'))

  if (Number.isNaN(parsedDate.getTime())) {
    return '-'
  }

  try {
    return new Intl.DateTimeFormat(currentLocale.value || 'en', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(parsedDate)
  } catch {
    return '-'
  }
})

// Remount the book detail page whenever the route's bookId changes so each
// book mini app starts with fresh local state, dialogs, and filters.
const routerViewKey = computed(() => {
  if (route.name === 'book-detail') {
    return String(route.params.bookId ?? 'book-detail')
  }

  return String(route.name ?? route.path)
})

function bookTypeRequiresCurrency(bookType) {
  // The backend sends `requires_currency`, so the UI can stay data-driven
  // instead of hardcoding which book types are "money" books.
  return Number(bookType?.requires_currency ?? 0) === 1
}

function normalizeOptionalTextInput(value) {
  // Keep optional text fields consistent before sending them to the API.
  return String(value ?? '').trim()
}

function findCurrencyOption(currencyCode) {
  const normalizedCurrencyCode = String(currencyCode ?? '').trim().toUpperCase()

  return BOOK_CURRENCY_OPTIONS.find((option) => option.code === normalizedCurrencyCode) ?? null
}

function formatCurrencyDisplay(currencyCode) {
  const option = findCurrencyOption(currencyCode)
  const normalizedCurrencyCode = String(currencyCode ?? '').trim()

  return option?.label ?? (normalizedCurrencyCode !== '' ? normalizedCurrencyCode : '-')
}

function hydrateBookSettingsForm() {
  // Settings form only edits mutable metadata, so we copy just title/description.
  bookSettingsForm.title = String(bookForSettings.value?.title ?? '')
  bookSettingsForm.description = String(bookForSettings.value?.description ?? '')
}

function resetBookSettingsForm() {
  bookSettingsForm.title = ''
  bookSettingsForm.description = ''
}

function handleCreateBookTypeChange(bookType) {
  // The create dialog is progressive: first choose a type, then reveal the
  // rest of the form and only show currency when that type requires it.
  showCreateBookFields.value = true
  showCreateBookCurrencyField.value = bookTypeRequiresCurrency(bookType)

  if (!showCreateBookCurrencyField.value) {
    createBookForm.currency_code = ''
  }
}

provide('openBookSettingsDialog', openBookSettingsDialog)

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

    errorMessage.value = booksStore.errorMessage || t('appLayout.unableDashboard')
  }
})

async function handleLogout() {
  isLoggingOut.value = true

  try {
    // Logout also clears the shared books cache so the next session starts clean.
    await authStore.logout()
    booksStore.reset()
    window.location.replace('/')
  } catch {
    errorMessage.value = t('appLayout.unableLogout')
  } finally {
    isLoggingOut.value = false
  }
}

function openProfileDialog() {
  hydrateProfileDialogForm()
  resetProfileDialogMessages()
  closePasswordUpdateForm()

  if (!profileDialog.value?.open) {
    profileDialog.value?.showModal()
  }
}

function closeProfileDialog() {
  if (profileDialog.value?.open) {
    profileDialog.value.close()
  }
}

function handleProfileDialogCancel(event) {
  event.preventDefault()

  if (isProfileActionPending.value) {
    return
  }

  closeProfileDialog()
}

function handleProfileDialogClose() {
  resetProfileDialogState()
}

function resetProfileDialogState() {
  activeProfileAction.value = ''
  hydrateProfileDialogForm()
  resetProfileDialogMessages()
  showPasswordUpdateForm.value = false
  resetPasswordForm()
}

function hydrateProfileDialogForm() {
  profileForm.name = String(user.value?.name ?? '')
  profileForm.phone = String(user.value?.phone ?? '')
}

function resetProfileDialogMessages() {
  profileErrorMessages.name = ''
  profileErrorMessages.phone = ''
  profileErrorMessages.password = ''
}

function openPasswordUpdateForm() {
  profileErrorMessages.password = ''
  resetPasswordForm()
  showPasswordUpdateForm.value = true
}

function closePasswordUpdateForm() {
  profileErrorMessages.password = ''
  showPasswordUpdateForm.value = false
  resetPasswordForm()
}

function resetPasswordForm() {
  passwordForm.current_password = ''
  passwordForm.new_password = ''
  passwordForm.new_password_confirmation = ''
}

async function handleProfileNameSave() {
  if (isNameSaveDisabled.value) {
    return
  }

  profileErrorMessages.name = ''
  activeProfileAction.value = 'name'

  try {
    await updateProfileRequest({
      name: profileForm.name.trim(),
    })
    closeProfileDialog()
    window.location.reload()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeProfileDialog()
      router.replace({ name: 'login' })
      return
    }

    profileErrorMessages.name = getApiErrorMessage(error, t('profileDialog.unableUpdateName'))
  } finally {
    activeProfileAction.value = ''
  }
}

async function handleProfilePhoneSave() {
  if (isPhoneSaveDisabled.value) {
    return
  }

  profileErrorMessages.phone = ''
  activeProfileAction.value = 'phone'

  try {
    await updateProfileRequest({
      phone: profileForm.phone.trim(),
    })
    closeProfileDialog()
    window.location.reload()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeProfileDialog()
      router.replace({ name: 'login' })
      return
    }

    profileErrorMessages.phone = getApiErrorMessage(error, t('profileDialog.unableUpdatePhone'))
  } finally {
    activeProfileAction.value = ''
  }
}

async function handleProfilePasswordSave() {
  if (isPasswordSaveDisabled.value) {
    return
  }

  profileErrorMessages.password = ''
  activeProfileAction.value = 'password'

  try {
    await updatePasswordRequest({
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation,
    })
    closeProfileDialog()
    window.location.reload()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeProfileDialog()
      router.replace({ name: 'login' })
      return
    }

    profileErrorMessages.password = getApiErrorMessage(error, t('profileDialog.unableUpdatePassword'))
  } finally {
    activeProfileAction.value = ''
  }
}

async function openCreateBookDialog() {
  createBookErrorMessage.value = ''

  if (!createBookDialog.value?.open) {
    createBookDialog.value?.showModal()
  }

  // Load active book types on demand so the dialog reflects backend rules and
  // labels without shipping a hardcoded frontend list.
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

function openBookSettingsDialog(book) {
  const dialog = bookSettingsDialog.value

  if (!dialog) {
    return
  }

  bookForSettings.value = book ?? null
  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = ''
  hydrateBookSettingsForm()

  if (!dialog.open) {
    dialog.showModal()
  }
}

function closeBookSettingsDialog() {
  const dialog = bookSettingsDialog.value

  if (dialog?.open) {
    dialog.close()
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
  bookForSettings.value = null
}

function resetBookSettingsDialogState() {
  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = ''
  resetBookSettingsForm()
}

async function handleUpdateBookSettings() {
  const bookId = String(bookForSettings.value?.id ?? '').trim()

  if (bookId === '' || isBookSettingsActionPending.value || bookSettingsForm.title.trim() === '') {
    return
  }

  bookSettingsErrorMessage.value = ''
  activeBookSettingsAction.value = 'save'

  try {
    await updateBookRequest(bookId, {
      title: bookSettingsForm.title.trim(),
      description: normalizeOptionalTextInput(bookSettingsForm.description),
    })

    closeBookSettingsDialog()
    window.location.reload()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeBookSettingsDialog()
      router.replace({ name: 'login' })
      return
    }

    bookSettingsErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableUpdateBook'))
  } finally {
    activeBookSettingsAction.value = ''
  }
}

async function handleArchiveBook() {
  const bookId = String(bookForSettings.value?.id ?? '').trim()

  if (bookId === '' || isBookSettingsActionPending.value) {
    return
  }

  if (!window.confirm(t('appLayout.confirmArchiveBook'))) {
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

    bookSettingsErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableArchiveBook'))
  } finally {
    activeBookSettingsAction.value = ''
  }
}

async function handleDeleteBook() {
  const bookId = String(bookForSettings.value?.id ?? '').trim()

  if (bookId === '' || isBookSettingsActionPending.value) {
    return
  }

  if (!window.confirm(t('appLayout.confirmDeleteBook'))) {
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

    bookSettingsErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableDeleteBook'))
  } finally {
    activeBookSettingsAction.value = ''
  }
}

async function loadBookTypes() {
  isLoadingBookTypes.value = true
  createBookErrorMessage.value = ''

  try {
    // The backend decides which types are active and whether they require
    // currency, so the dialog always uses server-provided capability data.
    const { data } = await fetchBookTypes()
    bookTypes.value = data.bookTypes ?? []
    hasLoadedBookTypes.value = true
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateBookDialog()
      router.replace({ name: 'login' })
      return
    }

    createBookErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableLoadBookTypes'))
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
    // Send currency only when the selected type actually shows that field in
    // the UI. Non-currency books stay simple and omit it entirely.
    const { data } = await createBookRequest({
      title: createBookForm.title.trim(),
      description: normalizeOptionalTextInput(createBookForm.description),
      type_key: createBookForm.type_key,
      ...(showCreateBookCurrencyField.value
        ? { currency_code: createBookForm.currency_code }
        : {}),
    })
    responseData = data
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeCreateBookDialog()
      router.replace({ name: 'login' })
      return
    }

    createBookErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableCreateBook'))
    return
  } finally {
    isCreatingBook.value = false
  }

  const createdBookId = typeof responseData?.book?.id === 'string'
    ? responseData.book.id.trim()
    : ''

  if (createdBookId === '') {
    createBookErrorMessage.value = t('appLayout.missingBookId')
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
  createBookForm.currency_code = ''
  showCreateBookFields.value = false
  showCreateBookCurrencyField.value = false
  createBookErrorMessage.value = ''
  isCreatingBook.value = false
}
</script>
