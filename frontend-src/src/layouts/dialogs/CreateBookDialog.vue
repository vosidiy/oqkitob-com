<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <div class="dialog-body">
      <button class="btn-close-dialog" :disabled="isCreatingBook" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
          <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
        </svg>
      </button>

      <header>
        <h4 class="mb-1">{{ $t('appLayout.createBookDialogTitle') }}</h4>
      </header>

      <hr>

      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="emit('submit')">
        <fieldset class="mb-4">
          <legend class="form-label mb-2">{{ $t('appLayout.chooseBookType') }}</legend>
          <div v-if="isLoadingBookTypes" class="text-secondary">{{ $t('appLayout.loadingBookTypes') }}</div>
          <div v-else-if="bookTypes.length === 0" class="text-secondary">
            {{ $t('appLayout.noBookTypes') }}
          </div>

          <div v-else class="gap-2">
            <div v-for="bookType in bookTypes" :key="bookType.type_key" class="mb-2">
              <label class="card-check w-full d-block p-3 rounded" :for="`book-type-${bookType.type_key}`">
                <input
                  :id="`book-type-${bookType.type_key}`"
                  v-model="form.type_key"
                  class="form-check-input float-right"
                  type="radio"
                  name="book-type"
                  :value="bookType.type_key"
                  :disabled="isCreatingBook"
                  @change="emit('type-change', bookType)"
                >
                <p class="font-semibold">📙 {{ $t('bookTypes.' + bookType.type_key) }}</p>
                <p v-if="bookType.description" class="small text-secondary mt-1">{{ bookType.description }}</p>
              </label>
            </div>
          </div>
        </fieldset>

        <div v-if="showCreateBookFields" class="mt-3">
          <div class="mb-2">
            <label class="form-label" for="create-book-title">{{ $t('common.fields.name') }}</label>
            <input
              id="create-book-title"
              v-model.trim="form.title"
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
              v-model.trim="form.description"
              class="form-control"
              rows="2"
              :placeholder="$t('appLayout.addBookDescription')"
              :disabled="isCreatingBook"
            ></textarea>
          </div>

          <div v-if="showCreateBookCurrencyField" class="mb-4">
            <label class="form-label" for="create-book-currency">{{ $t('common.fields.currency') }}</label>
            <select
              id="create-book-currency"
              v-model="form.currency_code"
              class="form-select max-w-50"
              :disabled="isCreatingBook"
            >
              <option value="" disabled>{{ $t('appLayout.selectCurrency') }}</option>
              <option
                v-for="currencyOption in currencyOptions"
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
          <button type="submit" class="btn flex-1 btn-primary" :disabled="isSubmitDisabled">
            <span v-if="isCreatingBook">{{ $t('common.states.creating') }}</span>
            <span v-else>{{ $t('common.actions.create') }}</span>
          </button>
          <button type="button" class="btn flex-1 btn-default" :disabled="isCreatingBook" @click="close">
            {{ $t('common.actions.cancel') }}
          </button>
        </footer>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  bookTypes: {
    type: Array,
    default: () => [],
  },
  currencyOptions: {
    type: Array,
    default: () => [],
  },
  errorMessage: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  isCreatingBook: {
    type: Boolean,
    default: false,
  },
  isLoadingBookTypes: {
    type: Boolean,
    default: false,
  },
  isSubmitDisabled: {
    type: Boolean,
    default: false,
  },
  showCreateBookCurrencyField: {
    type: Boolean,
    default: false,
  },
  showCreateBookFields: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['cancel', 'close', 'submit', 'type-change'])
const dialogRef = ref(null)

function open() {
  if (!dialogRef.value?.open) {
    dialogRef.value?.showModal()
  }
}

function close() {
  if (dialogRef.value?.open) {
    dialogRef.value.close()
  }
}

function isOpen() {
  return dialogRef.value?.open === true
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
