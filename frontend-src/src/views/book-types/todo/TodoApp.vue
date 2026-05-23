<template>
  <div class="d-flex flex-col h-full w-full">
    <BookPageHeader :book="book" />

    <div class="p-5">
      <h3 class="h6 mb-3">{{ $t('todo.title') }}</h3>

      <div v-if="isLoading" class="text-secondary">{{ $t('todo.loading') }}</div>

      <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
        {{ errorMessage }}
      </div>

      <div v-else-if="todos.length === 0" class="text-secondary">{{ $t('todo.empty') }}</div>

      <div v-else class="list-group">
        <div v-for="item in todos" :key="item.id" class="list-group-item">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">{{ item.title }}</div>
              <div v-if="item.description" class="small text-secondary mt-1">{{ item.description }}</div>
            </div>
            <span class="badge" :class="item.is_completed ? 'text-bg-success' : 'text-bg-warning'">
              {{ item.is_completed ? $t('todo.status.done') : $t('todo.status.open') }}
            </span>
          </div>
          <div class="small text-secondary mt-2 text-capitalize">
            {{ $t('todo.priority') }}: {{ $t('todo.priorities.' + item.priority) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { isUnauthorizedError } from '@/api/errors'
import { fetchTodos } from '@/api/todos'
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

const todos = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const hasStartedLoadingTodos = ref(false)

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

  if (hasStartedLoadingTodos.value) {
    return
  }

  hasStartedLoadingTodos.value = true
  isLoading.value = true
  errorMessage.value = ''

  try {
    const { data } = await fetchTodos(props.book.id)
    todos.value = data.todos ?? []
  } catch (error) {
    if (isUnauthorizedError(error)) {
      router.replace({ name: 'login' })
      return
    }

    errorMessage.value = t('todo.unableLoad')
  } finally {
    isLoading.value = false
  }
}, { immediate: true })
</script>
