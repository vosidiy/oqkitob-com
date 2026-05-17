<template>
  <main class="min-h-100 overflow-y-scroll py-5">
    <div class="container">

      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>
      
      <div v-if="isLoading" class="mt-10 text-secondary">
          <p class="skeleton"> <b> Loading...</b> <b>Wait...</b>  <b> ... </b> </p>
      </div>
      <div v-else-if="notes.length === 0" class="mt-10 text-lg text-secondary"> No notes yet.  Click “Add new note” to get started. </div>

      <div v-else class="d-grid grid-template-cols-4 mobile:grid-template-cols-1 tablet:grid-template-cols-2 gap-5">

        <article @click="openCreateDialog" class="card min-h-50 cursor-text border-dashed hover:border-color-neutral-600 shadow p-7" style="flex-basis: 240px;">
          <h5 class="font-medium text-secondary mb-2"> Add new note </h5>
          <div class="text-muted text-lg"> ✍️ Enter note details </div>
        </article>

        <article v-for="item in notes" :key="item.id" class="card group hover:border-color-neutral-600 shadow-sm" :class="getNoteCardClass(item.color)">
            <div class="p-5 cursor-pointer" style="height: 160px;" @click="openEditDialog(item)" :disabled="isNoteBusy(item.id)">
                <h5 class="font-medium mb-2">{{ item.title || 'Untitled note' }}</h5>
                <div v-if="item.content" class="opacity-70 overflow-hidden">
                  {{ item.content }}
                </div>
                <p v-else class="text-muted"> --- </p>
            </div>
            <div class="d-flex p-2 gap-1">
                <button type="button"  class="btn btn-sm btn-icon btn-neutral" @click="handleArchiveNote(item)" :disabled="isNoteBusy(item.id)">
                  🗑️
                </button>
                <button type="button" class="btn btn-sm btn-icon  btn-neutral"
                    @click="handleDeleteNote(item)" :disabled="isNoteBusy(item.id)">
                    ❌
                </button>
                <button type="button" class="btn btn-sm btn-icon" :class="isPinned(item) ? 'btn-default' : 'btn-neutral'" 
                  @click="handleTogglePin(item)" :disabled="isNoteBusy(item.id)">
                    📌
                </button>
              </div>
        </article>
      </div>

    </div> 
    <!-- container .//end -->
  </main>

  <dialog
      ref="createDialog"
      @cancel="handleCreateDialogCancel"
      @close="handleCreateDialogClose"
    >
      <form class="m-0" @submit.prevent="handleCreateNote">
        <div class="border-bottom px-4 py-3">
          <h2 class="h5 mb-1">Create Note</h2>
          <p class="text-secondary mb-0">Add a title, content, and optional color.</p>
        </div>

        <div class="px-4 py-3">
          <div v-if="createErrorMessage" class="alert alert-danger" role="alert">
            {{ createErrorMessage }}
          </div>

          <div class="mb-3">
            <label class="form-label" for="create-note-title">Title</label>
            <input
              id="create-note-title"
              v-model.trim="createForm.title"
              type="text"
              class="form-control"
              placeholder="Enter note title"
              :disabled="isCreatingNote"
            >
          </div>

          <div class="mb-3">
            <label class="form-label" for="create-note-content">Content</label>
            <textarea
              id="create-note-content"
              v-model.trim="createForm.content"
              class="form-control"
              rows="5"
              placeholder="Write your note"
              :disabled="isCreatingNote"
            ></textarea>
          </div>

          <div class="mb-0">
            <div class="form-label mb-2">Color</div>
            <div class="vstack gap-2">
              <div
                v-for="option in colorOptions"
                :key="`create-${option.value || 'white'}`"
                class="form-check border rounded px-3 py-2"
              >
                <input
                  :id="`create-note-color-${option.value || 'white'}`"
                  v-model="createForm.color"
                  class="form-check-input"
                  type="radio"
                  name="create-note-color"
                  :value="option.value"
                  :disabled="isCreatingNote"
                >
                <label
                  class="form-check-label w-100"
                  :for="`create-note-color-${option.value || 'white'}`"
                >
                  {{ option.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline" @click="closeCreateDialog" :disabled="isCreatingNote">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isCreatingNote">
            <span v-if="isCreatingNote">Saving...</span>
            <span v-else>Save</span>
          </button>
        </div>
      </form>
    </dialog>

    <dialog
      ref="editDialog"
      @cancel="handleEditDialogCancel"
      @close="handleEditDialogClose"
    >
      <form class="m-0" @submit.prevent="handleUpdateNote">
        <div class="border-bottom px-4 py-3">
          <h2 class="h5 mb-1">Edit Note</h2>
          <p class="text-secondary mb-0">Update the title, content, or note color.</p>
        </div>

        <div class="px-4 py-3">
          <div v-if="editErrorMessage" class="alert alert-danger" role="alert">
            {{ editErrorMessage }}
          </div>

          <div class="mb-3">
            <label class="form-label" for="edit-note-title">Title</label>
            <input
              id="edit-note-title"
              v-model.trim="editForm.title"
              type="text"
              class="form-control"
              placeholder="Enter note title"
              :disabled="isUpdatingNote"
            >
          </div>

          <div class="mb-3">
            <label class="form-label" for="edit-note-content">Content</label>
            <textarea
              id="edit-note-content"
              v-model.trim="editForm.content"
              class="form-control"
              rows="5"
              placeholder="Write your note"
              :disabled="isUpdatingNote"
            ></textarea>
          </div>
          <div class="mb-0">
            <div class="form-label mb-2">Color</div>
            <div class="vstack gap-2">
              <div
                v-for="option in colorOptions"
                :key="`edit-${option.value || 'white'}`"
                class="form-check border rounded px-3 py-2"
              >
                <input
                  :id="`edit-note-color-${option.value || 'white'}`"
                  v-model="editForm.color"
                  class="form-check-input"
                  type="radio"
                  name="edit-note-color"
                  :value="option.value"
                  :disabled="isUpdatingNote"
                >
                <label
                  class="form-check-label w-100"
                  :for="`edit-note-color-${option.value || 'white'}`"
                >
                  {{ option.label }}
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="border-top px-4 py-3 d-flex justify-content-end gap-2">          
          <button type="button" class="btn btn-outline" @click="closeEditDialog" :disabled="isUpdatingNote">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isUpdatingNote">
            <span v-if="isUpdatingNote">Saving...</span>
            <span v-else>Save</span>
          </button>
        </div>
      </form>
    </dialog>

</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
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

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const colorOptions = [
  { value: '', label: 'White' },
  { value: 'yellow', label: 'Yellow' },
  { value: 'blue', label: 'Blue' },
  { value: 'green', label: 'Green' },
  { value: 'red', label: 'Red' },
]

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

const createForm = reactive({
  title: '',
  content: '',
  color: '',
})

const editForm = reactive({
  title: '',
  content: '',
  color: '',
})

onMounted(async () => {
  await loadNotes()
})

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

    errorMessage.value = 'Unable to load notes.'
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
  if (createDialog.value?.open) {
    createDialog.value.close()
  }
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
  editForm.color = note.color ?? ''
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

    createErrorMessage.value = getApiErrorMessage(error, 'Unable to create note right now.')
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

    editErrorMessage.value = getApiErrorMessage(error, 'Unable to update note right now.')
  } finally {
    isUpdatingNote.value = false
  }
}

async function handleArchiveNote(note) {
  if (!window.confirm('Are you sure?')) {
    return
  }

  errorMessage.value = ''
  activeNoteActionId.value = note.id

  try {
    const { data } = await archiveNoteRequest(props.book.id, note.id)
    removeNoteFromList(data.noteId ?? note.id)
  } catch (error) {
    if (await handleUnauthorized(error)) {
      return
    }

    errorMessage.value = getApiErrorMessage(error, 'Unable to archive note right now.')
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

    errorMessage.value = getApiErrorMessage(error, 'Unable to update note pin right now.')
  } finally {
    activeNoteActionId.value = ''
  }
}

async function handleDeleteNote(note) {
  if (!window.confirm('Are you sure?')) {
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

    errorMessage.value = getApiErrorMessage(error, 'Unable to delete note right now.')
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
    window.alert('Please enter a title or note content.')
    return false
  }

  return true
}

function resetCreateForm() {
  createForm.title = ''
  createForm.content = ''
  createForm.color = ''
  createErrorMessage.value = ''
  isCreatingNote.value = false
}

function resetEditForm() {
  editForm.title = ''
  editForm.content = ''
  editForm.color = ''
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
  switch (color) {
    case 'purple':
      return ['bg-purple-100', 'border-purple-red-300']
    case 'blue':
      return ['bg-blue-100', 'border-color-blue-300']
    case 'green':
      return ['bg-green-100', 'border-color-green-300']
    case 'red':
      return ['bg-red-100', 'border-color-red-300']
    default:
      return ['bg-yellow-100', 'border-color-yellow-300']
  }
}
</script>
