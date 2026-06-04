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
            <small class="text-sm text-secondary">{{ user?.phone || '-' }}</small>
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
                <p class="text-secondary  mt-1"> [{{ $t('bookTypes.' + book.type_key) }}]</p>
            </div>
            
          </RouterLink>
        </div>
        <button class="btn btn-default w-full mt-2" @click="openCreateBookDialog"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text-icon lucide-book-text"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"></path><path d="M8 11h8"></path><path d="M8 7h6"></path></svg> {{ $t('appLayout.createBook') }} </button>
        
        <p v-if="booksStore.hasArchivedBooks" class="text-center mt-3">
          <a href="#" class="text-secondary text-sm" @click.prevent="openArchivedBooksDialog">
            {{ $t('appLayout.archivedBooks') }}
          </a>
        </p>
    
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

  <ProfileDialog
    ref="profileDialog"
    :formatted-profile-created-at="formattedProfileCreatedAt"
    :is-logging-out="isLoggingOut"
    :is-name-save-disabled="isNameSaveDisabled"
    :is-password-save-disabled="isPasswordSaveDisabled"
    :is-phone-save-disabled="isPhoneSaveDisabled"
    :is-profile-action-pending="isProfileActionPending"
    :is-saving-profile-name="isSavingProfileName"
    :is-saving-profile-password="isSavingProfilePassword"
    :is-saving-profile-phone="isSavingProfilePhone"
    :password-form="passwordForm"
    :profile-error-messages="profileErrorMessages"
    :profile-form="profileForm"
    :show-password-update-form="showPasswordUpdateForm"
    :user="user"
    @cancel="handleProfileDialogCancel"
    @close="handleProfileDialogClose"
    @close-password-form="closePasswordUpdateForm"
    @logout="handleLogout"
    @open-password-form="openPasswordUpdateForm"
    @save-name="handleProfileNameSave"
    @save-password="handleProfilePasswordSave"
    @save-phone="handleProfilePhoneSave"
  />

  <CreateBookDialog
    ref="createBookDialog"
    :book-types="bookTypes"
    :currency-options="BOOK_CURRENCY_OPTIONS"
    :error-message="createBookErrorMessage"
    :form="createBookForm"
    :is-creating-book="isCreatingBook"
    :is-loading-book-types="isLoadingBookTypes"
    :is-submit-disabled="isCreateBookSubmitDisabled"
    :show-create-book-currency-field="showCreateBookCurrencyField"
    :show-create-book-fields="showCreateBookFields"
    @cancel="handleCreateBookDialogCancel"
    @close="handleCreateBookDialogClose"
    @submit="handleCreateBook"
    @type-change="handleCreateBookTypeChange"
  />

  <BookSettingsDialog
    ref="bookSettingsDialog"
    :active-book-settings-action="activeBookSettingsAction"
    :book="bookForSettings"
    :error-message="bookSettingsErrorMessage"
    :form="bookSettingsForm"
    :format-currency-display="formatCurrencyDisplay"
    :is-book-settings-action-pending="isBookSettingsActionPending"
    :is-saving-book-settings="isSavingBookSettings"
    @archive="handleArchiveBook"
    @cancel="handleBookSettingsDialogCancel"
    @close="handleBookSettingsDialogClose"
    @save="handleUpdateBookSettings"
  />

  <ArchivedBooksDialog
    ref="archivedBooksDialog"
    :active-archived-book-action="activeArchivedBookAction"
    :active-archived-book-id="activeArchivedBookId"
    :archived-books="booksStore.archivedBooks"
    :error-message="archivedBooksDialogErrorMessage"
    :is-archived-book-action-pending="isArchivedBookActionPending"
    :is-archived-book-busy="isArchivedBookBusy"
    :is-loading-archived="booksStore.isLoadingArchived"
    @cancel="handleArchivedBooksDialogCancel"
    @close="handleArchivedBooksDialogClose"
    @delete="handleDeleteArchivedBook"
    @restore="handleRestoreArchivedBook"
  />

</template>

<script setup>
import { computed, onMounted, provide, reactive, ref } from 'vue'
import { useRoute, useRouter, RouterLink, RouterView } from 'vue-router'
import {
  archiveBookRequest,
  createBookRequest,
  deleteBookRequest,
  fetchBookTypes,
  restoreBookRequest,
  updateBookRequest,
} from '@/api/books-api'
import { updatePasswordRequest, updateProfileRequest } from '@/api/auth'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import { useI18n } from 'vue-i18n'
import { useLocale } from '@/composables/use-locale'
import { authStore } from '@/stores/auth'
import { useBooksStore } from '@/stores/books-store'
import { formatDate } from '@/utils/date-time'
import ArchivedBooksDialog from './dialogs/ArchivedBooksDialog.vue'
import BookSettingsDialog from './dialogs/BookSettingsDialog.vue'
import CreateBookDialog from './dialogs/CreateBookDialog.vue'
import ProfileDialog from './dialogs/ProfileDialog.vue'

const BOOK_CURRENCY_OPTIONS = [
  { code: 'EUR', label: '🇪🇺 EUR (€) - Euro' },
  { code: 'RUB', label: '🇷🇺 RUB (₽) - Russian ruble' },
  { code: 'UZS', label: '🇺🇿 UZS - Uzbekistan som' },
  { code: 'USD', label: '🇺🇸 USD ($) - US Dollar' },
]
const DEFAULT_BOOK_MONEY_SETTINGS = Object.freeze({
  thousand_separator: 'comma',
  show_cents: true,
})

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
const archivedBooksDialog = ref(null)
const bookTypes = ref([])
const hasLoadedBookTypes = ref(false)
const isLoadingBookTypes = ref(false)
const isCreatingBook = ref(false)
const createBookErrorMessage = ref('')
const bookSettingsErrorMessage = ref('')
const archivedBooksDialogErrorMessage = ref('')
const activeBookSettingsAction = ref('')
const activeProfileAction = ref('')
const activeArchivedBookAction = ref('')
const activeArchivedBookId = ref('')
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
  thousand_separator: DEFAULT_BOOK_MONEY_SETTINGS.thousand_separator,
  show_cents: DEFAULT_BOOK_MONEY_SETTINGS.show_cents,
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
const isArchivedBookActionPending = computed(() => activeArchivedBookAction.value !== '')
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
  return formatDate(user.value?.created_at, { locale: currentLocale.value })
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

function applyBookMoneyDisplaySettings(book) {
  bookSettingsForm.thousand_separator = String(
    book?.thousand_separator ?? DEFAULT_BOOK_MONEY_SETTINGS.thousand_separator
  ).trim() || DEFAULT_BOOK_MONEY_SETTINGS.thousand_separator
  bookSettingsForm.show_cents = !['0', 0, false].includes(book?.show_cents ?? DEFAULT_BOOK_MONEY_SETTINGS.show_cents)
}

function hydrateBookSettingsForm() {
  // Settings form only edits mutable metadata and top-level money settings.
  bookSettingsForm.title = String(bookForSettings.value?.title ?? '')
  bookSettingsForm.description = String(bookForSettings.value?.description ?? '')
  applyBookMoneyDisplaySettings(bookForSettings.value)
}

function resetBookSettingsForm() {
  bookSettingsForm.title = ''
  bookSettingsForm.description = ''
  applyBookMoneyDisplaySettings(null)
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
    return
  }

  try {
    await booksStore.fetchArchivedBooks()
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
    }
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

  if (!profileDialog.value?.isOpen()) {
    profileDialog.value?.open()
  }
}

function closeProfileDialog() {
  if (profileDialog.value?.isOpen()) {
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

  if (! isValidOptionalInternationalPhone(profileForm.phone.trim())) {
    profileErrorMessages.phone = t('profileDialog.validationPhoneInvalid')
    return
  }

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

function isValidOptionalInternationalPhone(phone) {
  if (phone === '') {
    return true
  }

  if (/\p{L}/u.test(phone)) {
    return false
  }

  const digits = phone.replace(/\D+/g, '')

  return digits.length >= 8 && digits.length <= 15 && digits[0] !== '0'
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

  if (!createBookDialog.value?.isOpen()) {
    createBookDialog.value?.open()
  }

  // Load active book types on demand so the dialog reflects backend rules and
  // labels without shipping a hardcoded frontend list.
  if (!hasLoadedBookTypes.value && !isLoadingBookTypes.value) {
    await loadBookTypes()
  }
}

function closeCreateBookDialog() {
  if (createBookDialog.value?.isOpen()) {
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

  if (!dialog.isOpen()) {
    dialog.open()
  }
}

function closeBookSettingsDialog() {
  const dialog = bookSettingsDialog.value

  if (dialog?.isOpen()) {
    dialog.close()
  }
}

async function openArchivedBooksDialog() {
  archivedBooksDialogErrorMessage.value = ''

  if (!archivedBooksDialog.value?.isOpen()) {
    archivedBooksDialog.value?.open()
  }

  try {
    await booksStore.fetchArchivedBooks(true)
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeArchivedBooksDialog()
      router.replace({ name: 'login' })
      return
    }

    archivedBooksDialogErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableLoadArchivedBooks'))
  }
}

function closeArchivedBooksDialog() {
  if (archivedBooksDialog.value?.isOpen()) {
    archivedBooksDialog.value.close()
  }
}

function handleArchivedBooksDialogCancel(event) {
  event.preventDefault()

  if (isArchivedBookActionPending.value) {
    return
  }

  closeArchivedBooksDialog()
}

function handleArchivedBooksDialogClose() {
  archivedBooksDialogErrorMessage.value = ''
  activeArchivedBookAction.value = ''
  activeArchivedBookId.value = ''
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
      thousand_separator: bookSettingsForm.thousand_separator,
      show_cents: bookSettingsForm.show_cents,
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
    await refreshBookLists()
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

function isArchivedBookBusy(bookId) {
  return isArchivedBookActionPending.value && activeArchivedBookId.value === String(bookId)
}

async function handleRestoreArchivedBook(book) {
  const bookId = String(book?.id ?? '').trim()

  if (bookId === '' || isArchivedBookActionPending.value) {
    return
  }

  if (!window.confirm(t('appLayout.confirmRestoreBook'))) {
    return
  }

  archivedBooksDialogErrorMessage.value = ''
  activeArchivedBookAction.value = 'restore'
  activeArchivedBookId.value = bookId

  try {
    await restoreBookRequest(bookId)
    await refreshBookLists()

    if (!booksStore.hasArchivedBooks) {
      closeArchivedBooksDialog()
    }
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeArchivedBooksDialog()
      router.replace({ name: 'login' })
      return
    }

    archivedBooksDialogErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableRestoreBook'))
  } finally {
    activeArchivedBookAction.value = ''
    activeArchivedBookId.value = ''
  }
}

async function handleDeleteArchivedBook(book) {
  const bookId = String(book?.id ?? '').trim()

  if (bookId === '' || isArchivedBookActionPending.value) {
    return
  }

  if (!window.confirm(t('appLayout.confirmDeleteBook'))) {
    return
  }

  archivedBooksDialogErrorMessage.value = ''
  activeArchivedBookAction.value = 'delete'
  activeArchivedBookId.value = bookId

  try {
    await deleteBookById(bookId)
    await refreshBookLists()

    if (!booksStore.hasArchivedBooks) {
      closeArchivedBooksDialog()
    }
  } catch (error) {
    if (isUnauthorizedError(error)) {
      closeArchivedBooksDialog()
      router.replace({ name: 'login' })
      return
    }

    archivedBooksDialogErrorMessage.value = getApiErrorMessage(error, t('appLayout.unableDeleteBook'))
  } finally {
    activeArchivedBookAction.value = ''
    activeArchivedBookId.value = ''
  }
}

async function refreshBookLists() {
  await Promise.all([
    booksStore.fetchBooks(true),
    booksStore.fetchArchivedBooks(true),
  ])
}

function deleteBookById(bookId) {
  return deleteBookRequest(bookId)
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
