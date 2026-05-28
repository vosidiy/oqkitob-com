<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('appLayout.archivedBooksTitle') }}</h5>
      <button class="btn btn-icon" :disabled="isArchivedBookActionPending" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
          <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
        </svg>
      </button>
    </header>
    <div class="dialog-body">
      <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
        {{ errorMessage }}
      </div>

      <div v-if="isLoadingArchived" class="text-secondary">
        {{ $t('appLayout.loadingArchivedBooks') }}
      </div>

      <div v-else-if="archivedBooks.length === 0" class="text-secondary">
        {{ $t('appLayout.noArchivedBooks') }}
      </div>

      <div v-else class="d-flex flex-col gap-2">
        <article
          v-for="book in archivedBooks"
          :key="book.id"
          class="card border p-3"
        >
          <div class="d-flex justify-content-between gap-3 mobile:flex-col">
            <div class="flex-1">
              <h6 class="mb-1">{{ book.title }}</h6>
              <p v-if="book.description" class="text-secondary mb-1">{{ book.description }}</p>
              <p class="text-secondary mb-0">{{ $t('bookTypes.' + book.type_key) }}</p>
            </div>

            <div class="d-flex gap-2 align-items-start mobile:w-full">
              <button
                type="button"
                class="btn btn-default"
                :disabled="isArchivedBookBusy(book.id)"
                @click="emit('restore', book)"
              >
                <span v-if="activeArchivedBookAction === 'restore' && activeArchivedBookId === book.id">{{ $t('common.states.restoring') }}</span>
                <span v-else>{{ $t('common.actions.restore') }}</span>
              </button>

              <button
                type="button"
                class="btn btn-red-subtle"
                :disabled="isArchivedBookBusy(book.id)"
                @click="emit('delete', book)"
              >
                <span v-if="activeArchivedBookAction === 'delete' && activeArchivedBookId === book.id">{{ $t('common.states.deleting') }}</span>
                <span v-else>{{ $t('common.actions.delete') }}</span>
              </button>
            </div>
          </div>
        </article>
      </div>

      <footer class="border-top pt-4 d-flex gap-2">
        <button
          type="button"
          class="btn flex-1 btn-default"
          :disabled="isArchivedBookActionPending"
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
  activeArchivedBookAction: {
    type: String,
    default: '',
  },
  activeArchivedBookId: {
    type: String,
    default: '',
  },
  archivedBooks: {
    type: Array,
    default: () => [],
  },
  errorMessage: {
    type: String,
    default: '',
  },
  isArchivedBookActionPending: {
    type: Boolean,
    default: false,
  },
  isArchivedBookBusy: {
    type: Function,
    required: true,
  },
  isLoadingArchived: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['cancel', 'close', 'delete', 'restore'])
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
