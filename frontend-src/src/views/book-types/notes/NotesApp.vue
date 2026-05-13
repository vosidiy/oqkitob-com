<template>
  <div>
    <h3 class="h6 mb-3">Notes</h3>

    <div v-if="isLoading" class="text-secondary">Loading notes...</div>

    <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
      {{ errorMessage }}
    </div>

    <div v-else-if="notes.length === 0" class="text-secondary">No notes yet.</div>

    <div v-else class="list-group">
      <div v-for="item in notes" :key="item.id" class="list-group-item">
        <div class="d-flex justify-content-between align-items-start gap-3">
          <div class="fw-semibold">{{ item.title || 'Untitled note' }}</div>
          <span v-if="item.is_pinned" class="badge text-bg-secondary">Pinned</span>
        </div>
        <div v-if="item.content" class="small text-secondary mt-2">{{ item.content }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { isUnauthorizedError } from '@/api/errors'
import { fetchNotes } from '@/api/notes'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const notes = ref([])
const isLoading = ref(true)
const errorMessage = ref('')

onMounted(async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const { data } = await fetchNotes(props.book.id)
    notes.value = data.notes ?? []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = 'Unable to load notes.'
  } finally {
    isLoading.value = false
  }
})
</script>
