<template>
  <div class="d-flex flex-col h-full w-full">
    <BookPageHeader :book="book" />

    <main class="min-h-100 overflow-y-scroll py-5 flex-1">
      <div class="container">

      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>
      
      <div v-if="isLoading" class="mt-10 text-secondary">
          <p class="skeleton"><b>{{ $t('notes.loadingDetailed') }}</b></p>
      </div>
      <div v-else-if="notes.length === 0" class="mt-10 text-lg text-secondary">{{ $t('notes.empty') }}</div>

      <div v-else class="d-grid grid-template-cols-4 mobile:grid-template-cols-1 tablet:grid-template-cols-2 gap-5">

        <article @click="openCreateDialog" class="card min-h-50 cursor-text border-dashed hover:border-color-neutral-600 shadow p-7" style="flex-basis: 240px;">
          <h5 class="font-medium text-secondary mb-2">{{ $t('notes.addNew') }}</h5>
          <div class="text-muted text-lg">✍️ {{ $t('notes.addNewHint') }}</div>
        </article>

        <article v-for="item in notes" :key="item.id" class="card group hover:border-color-neutral-600 shadow-sm" :class="getNoteCardClass(item.color)">
            <div class="p-5 cursor-pointer" style="height: 160px;" @click="openEditDialog(item)" :disabled="isNoteBusy(item.id)">
                <h5 v-if="item.title" class="font-medium mb-2">{{ item.title || $t('notes.untitled') }}</h5>
                <div v-if="item.content" class="opacity-70 overflow-hidden" style="height: 110px;">
                  {{ item.content }}
                </div>
                <p v-else class="text-muted"> --- </p>
            </div>
            <div class="d-flex p-3 gap-1">
               <button type="button" class="btn btn-sm btn-icon" :class="isPinned(item) ? 'btn-default' : 'btn-neutral'" 
                  @click="handleTogglePin(item)" :disabled="isNoteBusy(item.id)">
                    📌
                </button>
                <button type="button"  class="btn btn-sm btn-icon btn-neutral" @click="handleArchiveNote(item)" :disabled="isNoteBusy(item.id)">
                  🗑️
                </button>
                <button type="button" hidden disabled class="btn btn-sm btn-icon  btn-neutral"
                    @click="handleDeleteNote(item)" :disabled="isNoteBusy(item.id)">
                    ❌
                </button>
              </div>
        </article>
      </div>

      </div>
      <!-- container .//end -->
    </main>

    <dialog ref="createDialog" class="mt-20" @cancel="handleCreateDialogCancel" @close="handleCreateDialogClose">
      <div class="dialog-body">
          <form id="formNoteCreate"  @submit.prevent="handleCreateNote">
            <div v-if="createErrorMessage" class="alert alert-danger" role="alert">
              {{ createErrorMessage }}
            </div>
            <div class="d-flex mb-3">
              <input type="text"
                autofocus
                v-model.trim="createForm.title"
                class="form-control border-transparent hover:border text-xl"
                :placeholder="$t('notes.titlePlaceholder')"
                :disabled="isCreatingNote"
              >
              <button type="button" tabindex="-1" class="btn btn-icon ml-2" @click="closeCreateDialog" :disabled="isCreatingNote">
                <svg viewBox="0 0 24 24" width="24" height="24">
                    <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
                </svg> 
              </button> 
            </div>

            <textarea
              id="create-note-content"
              v-model.trim="createForm.content"
              class="form-control border-transparent hover:border text-lg h-auto"
              rows="5"
              :placeholder="$t('notes.contentPlaceholder')"
              :disabled="isCreatingNote"
            ></textarea>
          

            <div class="d-flex mt-3 gap-2">
              <label
                v-for="option in colorOptions"
                :key="`create-${option.value || 'white'}`"
                class="card-check p-1 rounded"
              >
                <input
                  v-model="createForm.color"
                  class="visually-hidden"
                  type="radio"
                  name="create-note-color"
                  :value="option.value"
                  :disabled="isCreatingNote"
                >
                <span  class="form-check-label">
                  {{ $t(option.labelKey) }}
                </span>
              </label>
            </div>
            
          </form>
      </div> <!-- dialog-body.// -->
      <div class="dialog-bottom">
          <button type="submit" form="formNoteCreate" class="btn flex-grow btn-primary" :disabled="isCreatingNote">
            <span v-if="isCreatingNote">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('common.actions.save') }}</span>
          </button>
          <button type="button" class="btn btn-default" @click="closeCreateDialog" :disabled="isCreatingNote">
            {{ $t('common.actions.cancel') }}
          </button>
      </div>
    </dialog>

    <dialog ref="editDialog" @cancel="handleEditDialogCancel" @close="handleEditDialogClose">
      <div class="dialog-body">
        <form id="formNoteEdit"  @submit.prevent="handleUpdateNote">
          <div v-if="editErrorMessage" class="alert alert-danger" role="alert">
            {{ editErrorMessage }}
          </div>

          <div class="d-flex mb-3">
            <input
              id="edit-note-title"
              v-model.trim="editForm.title"
              type="text"
              class="form-control"
              :placeholder="$t('notes.titlePlaceholder')"
              :disabled="isUpdatingNote"
            >
            <button type="button" tabindex="-1" class="btn btn-icon ml-2" @click="closeEditDialog" :disabled="isUpdatingNote">
              <svg viewBox="0 0 24 24" width="24" height="24">
                  <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path>
              </svg> 
            </button> 
          </div>
          
          <textarea
            id="edit-note-content"
            v-model.trim="editForm.content"
            class="form-control border-transparent hover:border text-lg h-auto"
            rows="5"
            autofocus
            :placeholder="$t('notes.contentPlaceholder')"
            :disabled="isUpdatingNote"
          ></textarea>

          <div class="d-flex mt-3 gap-2">
            <label
              v-for="option in colorOptions"
              :key="`edit-${option.value || 'yellow'}`"
              class="card-check p-1 rounded"
            >
              <input
                v-model="editForm.color"
                class="visually-hidden"
                type="radio"
                name="edit-note-color"
                :value="option.value"
                :disabled="isUpdatingNote"
              >
              <span  class="form-check-label">
                {{ $t(option.labelKey) }}
              </span>
            </label>
          </div>

        </form>
      </div> <!-- dialog-body .// --> 
      <div class="dialog-bottom">
        <button type="submit" form="formNoteEdit" class="btn flex-grow  btn-primary" :disabled="isUpdatingNote">
          <span v-if="isUpdatingNote">{{ $t('common.states.saving') }}</span>
          <span v-else>{{ $t('common.actions.save') }}</span>
        </button>
        <button
          type="button"
          class="btn btn-default"
          @click="handleArchiveNote(activeEditingNote)"
          :disabled="!activeEditingNote || isNoteBusy(activeEditingNote.id)"
        >
          🗑️ {{ $t('common.actions.archive') }}
        </button>
        <button type="button" class="btn btn-default" @click="closeEditDialog" :disabled="isUpdatingNote">
          {{ $t('common.actions.cancel') }}
        </button>
      </div>
    </dialog>

  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getApiErrorMessage, isUnauthorizedError } from '@/api/errors'
import {
  archiveNoteRequest,
  createNoteRequest,
  deleteNoteRequest,
  fetchNotes,
  pinNoteRequest,
  unpinNoteRequest,
  updateNoteRequest,
} from '@/api/notes'
import BookPageHeader from '@/components/BookPageHeader.vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const route = useRoute()
const router = useRouter()
const { t } = useI18n()

const DEFAULT_NOTE_COLOR = 'yellow'
const colorOptions = [
  { value: 'yellow', labelKey: 'notes.colors.yellow' },
  { value: 'purple', labelKey: 'notes.colors.purple' },
  { value: 'white', labelKey: 'notes.colors.white' },
  { value: 'blue', labelKey: 'notes.colors.blue' },
  { value: 'green', labelKey: 'notes.colors.green' }, 
  { value: 'red', labelKey: 'notes.colors.red' }, 
]
const supportedNoteColors = new Set(colorOptions.map((option) => option.value))

const notes = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const createDialog = ref(null)
const editDialog = ref(null)
const isCreatingNote = ref(false)
const isUpdatingNote = ref(false)
const activeNoteActionId = ref('')
const createErrorMessage = ref('')
const editErrorMessage = ref('')
const editingNoteId = ref('')
const hasStartedLoadingNotes = ref(false)

const createForm = reactive({
  title: '',
  content: '',
  color: '',
})

const editForm = reactive({
  title: '',
  content: '',
  color: DEFAULT_NOTE_COLOR,
})

const activeEditingNote = computed(() => {
  if (editingNoteId.value === '') {
    return null
  }

  return notes.value.find((item) => item.id === editingNoteId.value) ?? null
})

watch(() => route.params.page, async (page) => {
  if (page) {
    await router.replace({
      name: 'book-detail',
      params: {
        bookId: props.book.id,
      },
    })
    return
  }

  if (hasStartedLoadingNotes.value) {
    return
  }

  hasStartedLoadingNotes.value = true
  await loadNotes()
}, { immediate: true })

async function loadNotes() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const { data } = await fetchNotes(props.book.id)
    notes.value = sortNotes(data.notes ?? [])
  } catch (error) {
    if (await handleUnauthorized(error)) {
      return
    }

    errorMessage.value = t('notes.unableLoad')
  } finally {
    isLoading.value = false
  }
}

function openCreateDialog() {
  createErrorMessage.value = ''

  if (!createDialog.value?.open) {
    createDialog.value?.showModal()
  }
}

function closeCreateDialog() {
      createDialog.value.close()
}

function handleCreateDialogClose() {
  resetCreateForm()
}

function handleCreateDialogCancel(event) {
  if (isCreatingNote.value) {
    event.preventDefault()
  }
}

function openEditDialog(note) {
  editingNoteId.value = note.id
  editForm.title = note.title ?? ''
  editForm.content = note.content ?? ''
  editForm.color = supportedNoteColors.has(String(note.color ?? '').trim().toLowerCase())
    ? String(note.color ?? '').trim().toLowerCase()
    : DEFAULT_NOTE_COLOR
  editErrorMessage.value = ''

  if (!editDialog.value?.open) {
    editDialog.value?.showModal()
  }
}

function closeEditDialog() {
  if (editDialog.value?.open) {
    editDialog.value.close()
  }
}

function handleEditDialogClose() {
  resetEditForm()
}

function handleEditDialogCancel(event) {
  if (isUpdatingNote.value) {
    event.preventDefault()
  }
}

async function handleCreateNote() {
  if (!validateNoteForm(createForm)) {
    return
  }

  createErrorMessage.value = ''
  isCreatingNote.value = true

  try {
    const { data } = await createNoteRequest(props.book.id, {
      title: createForm.title,
      content: createForm.content,
      color: createForm.color,
    })

    if (data.note) {
      upsertNote(data.note)
    }

    closeCreateDialog()
  } catch (error) {
    if (await handleUnauthorized(error, closeCreateDialog)) {
      return
    }

    createErrorMessage.value = getApiErrorMessage(error, t('notes.unableCreate'))
  } finally {
    isCreatingNote.value = false
  }
}

async function handleUpdateNote() {
  if (editingNoteId.value === '') {
    return
  }

  if (!validateNoteForm(editForm)) {
    return
  }

  editErrorMessage.value = ''
  isUpdatingNote.value = true

  try {
    const { data } = await updateNoteRequest(props.book.id, editingNoteId.value, {
      title: editForm.title,
      content: editForm.content,
      color: editForm.color,
    })

    if (data.note) {
      upsertNote(data.note)
    }

    closeEditDialog()
  } catch (error) {
    if (await handleUnauthorized(error, closeEditDialog)) {
      return
    }

    editErrorMessage.value = getApiErrorMessage(error, t('notes.unableUpdate'))
  } finally {
    isUpdatingNote.value = false
  }
}

async function handleArchiveNote(note) {
  if (!note) {
    return
  }

  if (!window.confirm(t('notes.confirmArchive'))) {
    return
  }

  errorMessage.value = ''
  editErrorMessage.value = ''
  activeNoteActionId.value = note.id

  try {
    const { data } = await archiveNoteRequest(props.book.id, note.id)
    removeNoteFromList(data.noteId ?? note.id)

    if (editingNoteId.value === note.id) {
      closeEditDialog()
    }
  } catch (error) {
    if (await handleUnauthorized(error)) {
      return
    }

    const nextErrorMessage = getApiErrorMessage(error, t('notes.unableArchive'))
    errorMessage.value = nextErrorMessage

    if (editingNoteId.value === note.id) {
      editErrorMessage.value = nextErrorMessage
    }
  } finally {
    activeNoteActionId.value = ''
  }
}

async function handleTogglePin(note) {
  errorMessage.value = ''
  activeNoteActionId.value = note.id

  try {
    const request = isPinned(note)
      ? unpinNoteRequest(props.book.id, note.id)
      : pinNoteRequest(props.book.id, note.id)

    const { data } = await request

    patchNoteInList(data.noteId ?? note.id, {
      is_pinned: data.is_pinned ?? (isPinned(note) ? 0 : 1),
      updated_at: data.updated_at ?? note.updated_at,
    })
  } catch (error) {
    if (await handleUnauthorized(error)) {
      return
    }

    errorMessage.value = getApiErrorMessage(error, t('notes.unablePin'))
  } finally {
    activeNoteActionId.value = ''
  }
}

async function handleDeleteNote(note) {
  if (!window.confirm(t('notes.confirmDelete'))) {
    return
  }

  errorMessage.value = ''
  activeNoteActionId.value = note.id

  try {
    const { data } = await deleteNoteRequest(props.book.id, note.id)
    removeNoteFromList(data.noteId ?? note.id)
  } catch (error) {
    if (await handleUnauthorized(error)) {
      return
    }

    errorMessage.value = getApiErrorMessage(error, t('notes.unableDelete'))
  } finally {
    activeNoteActionId.value = ''
  }
}

async function handleUnauthorized(error, beforeRedirect) {
  if (!isUnauthorizedError(error)) {
    return false
  }

  if (typeof beforeRedirect === 'function') {
    beforeRedirect()
  }

  await router.replace({ name: 'login' })
  return true
}

function validateNoteForm(form) {
  if (form.title.trim() === '' && form.content.trim() === '') {
    window.alert(t('notes.validation'))
    return false
  }

  return true
}

function resetCreateForm() {
  createForm.title = ''
  createForm.content = ''
  createForm.color = DEFAULT_NOTE_COLOR
  createErrorMessage.value = ''
  isCreatingNote.value = false
}

function resetEditForm() {
  editForm.title = ''
  editForm.content = ''
  editForm.color = DEFAULT_NOTE_COLOR
  editErrorMessage.value = ''
  editingNoteId.value = ''
  isUpdatingNote.value = false
}

function upsertNote(note) {
  const nextNotes = notes.value.filter((item) => item.id !== note.id)

  if (Number(note.is_archived ?? 0) === 0) {
    nextNotes.push(note)
  }

  notes.value = sortNotes(nextNotes)
}

function patchNoteInList(noteId, patch) {
  notes.value = sortNotes(
    notes.value.map((item) => {
      if (item.id !== noteId) {
        return item
      }

      return {
        ...item,
        ...patch,
      }
    }),
  )
}

function removeNoteFromList(noteId) {
  notes.value = notes.value.filter((item) => item.id !== noteId)
}

function sortNotes(items) {
  return [...items].sort((left, right) => {
    if (Number(left.is_pinned ?? 0) !== Number(right.is_pinned ?? 0)) {
      return Number(right.is_pinned ?? 0) - Number(left.is_pinned ?? 0)
    }

    if (Number(left.position ?? 0) !== Number(right.position ?? 0)) {
      return Number(left.position ?? 0) - Number(right.position ?? 0)
    }

    return String(right.created_at ?? '').localeCompare(String(left.created_at ?? ''))
  })
}

function isNoteBusy(noteId) {
  return activeNoteActionId.value === noteId || isUpdatingNote.value
}

function isPinned(note) {
  return Number(note?.is_pinned ?? 0) === 1
}

function getNoteCardClass(color) {
  const normalizedColor = String(color ?? '').trim().toLowerCase()
  const noteColor = supportedNoteColors.has(normalizedColor) ? normalizedColor : DEFAULT_NOTE_COLOR

  switch (noteColor) {
    case 'purple':
      return ['bg-purple-100', 'border-color-purple-300']
    case 'blue':
      return ['bg-blue-100', 'border-color-blue-300']
    case 'green':
      return ['bg-green-100', 'border-color-green-300']
    case 'red':
      return ['bg-red-100', 'border-color-red-300']
    case 'white':
      return ['bg-neutral-100', 'border-color-neutral-300']
    default:
      return ['bg-yellow-100', 'border-color-yellow-300']
  }
}
</script>
