<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <div class="dialog-body">
      <button class="btn-close-dialog" :disabled="isBookSettingsActionPending" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
          <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
        </svg>
      </button>

      <header>
        <h4 class="mb-1">{{ $t('appLayout.bookSettingsTitle') }}</h4>
      </header>

      <hr>

      <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
        {{ errorMessage }}
      </div>

      <div class="card p-3 mb-2">
        <p class="text-secondary">{{ $t('appLayout.chooseBookType') }}</p>
        <p class="font-semibold">
          📙 {{ book?.type_key ? $t('bookTypes.' + book.type_key) : '-' }}
        </p>
      </div>

      <div class="mb-2">
        <label class="form-label" for="book-settings-title">{{ $t('common.fields.name') }}</label>
        <input
          id="book-settings-title"
          v-model.trim="form.title"
          type="text"
          class="form-control"
          :placeholder="$t('appLayout.enterBookName')"
          :disabled="isBookSettingsActionPending"
        >
      </div>

      <div class="mb-4">
        <textarea
          id="book-settings-description"
          v-model.trim="form.description"
          class="form-control"
          rows="2"
          :placeholder="$t('appLayout.addBookDescription')"
          :disabled="isBookSettingsActionPending"
        ></textarea>
      </div>

      <div v-if="book?.currency_code">
        <p class="mb-0">{{ $t('common.fields.currency') }}: {{ formatCurrencyDisplay(book?.currency_code) }}</p>
      </div>
      <p class="text-sm text-secondary mb-4">{{ $t('appLayout.bookCurrencyLockedHint') }}</p>

      <div v-if="showMoneyDisplaySettings" class="card p-3 mb-4">
        <h6 class="mb-3">{{ $t('appLayout.moneyDisplaySettingsTitle') }}</h6>

        <div class="mb-3">
          <label class="form-label" for="book-settings-thousand-separator">
            {{ $t('appLayout.thousandSeparatorLabel') }}
          </label>
          <select
            id="book-settings-thousand-separator"
            v-model="form.settings.money_display.thousand_separator"
            class="form-select"
            :disabled="isBookSettingsActionPending"
          >
            <option
              v-for="separatorOption in moneyDisplaySeparatorOptions"
              :key="separatorOption"
              :value="separatorOption"
            >
              {{ formatMoneyDisplayOptionLabel(separatorOption) }}
            </option>
          </select>
        </div>

        <label class="form-check">
          <input
            v-model="form.settings.money_display.show_cents"
            class="form-check-input"
            type="checkbox"
            :disabled="isBookSettingsActionPending"
          >
          <span class="ml-2">{{ $t('appLayout.showCentsLabel') }}</span>
        </label>
      </div>

      <div class="card border-red p-2 flex-row gap-2">
        <button
          type="button"
          class="btn btn-neutral text-red btn-sm"
          :disabled="!book?.id || isBookSettingsActionPending"
          @click="emit('archive')"
        >
          <span v-if="activeBookSettingsAction === 'archive'">{{ $t('common.states.archiving') }}</span>
          <span v-else>{{ $t('appLayout.archiveBook') }}</span>
        </button>
      </div>

      <footer class="border-top pt-4 d-flex gap-2">
        <button
          type="button"
          class="btn flex-1 btn-primary"
          :disabled="!book?.id || isBookSettingsActionPending || form.title.trim() === ''"
          @click="emit('save')"
        >
          <span v-if="isSavingBookSettings">{{ $t('common.states.saving') }}</span>
          <span v-else>{{ $t('common.actions.saveChanges') }}</span>
        </button>
        <button
          type="button"
          class="btn flex-1 btn-default"
          :disabled="isBookSettingsActionPending"
          @click="close"
        >
          {{ $t('common.actions.close') }}
        </button>
      </footer>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  activeBookSettingsAction: {
    type: String,
    default: '',
  },
  book: {
    type: Object,
    default: null,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  formatCurrencyDisplay: {
    type: Function,
    required: true,
  },
  formatMoneyDisplayOptionLabel: {
    type: Function,
    required: true,
  },
  isBookSettingsActionPending: {
    type: Boolean,
    default: false,
  },
  isSavingBookSettings: {
    type: Boolean,
    default: false,
  },
  moneyDisplaySeparatorOptions: {
    type: Array,
    default: () => [],
  },
  showMoneyDisplaySettings: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['archive', 'cancel', 'close', 'save'])
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
