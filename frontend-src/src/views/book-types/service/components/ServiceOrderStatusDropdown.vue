<template>
  <div ref="rootRef" class="relative">
    <button
      type="button"
      class="btn btn-neutral"
      :disabled="isDisabled || isUpdating"
      :title="$t('service.orders.changeStatus')"
      @click="toggleMenu"
    >
      <span>{{ $t('common.fields.status') }}:</span>
      <span class="d-inline-flex align-items-center gap-2" :class="statusMeta.textClass">
        <span>{{ statusMeta.emoji }}</span>
        <span>{{ $t(statusMeta.labelKey) }}</span>
      </span>
      <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor" aria-hidden="true">
        <path d="M480-344 240-584l56-56 184 184 184-184 56 56-240 240Z" />
      </svg>
    </button>

    <nav v-if="isOpen && statusOptions.length > 0" class="dropdown w-full" :aria-label="$t('service.orders.changeStatus')">
      <button
        v-for="option in statusOptions"
        :key="option.value"
        type="button"
        class="dropdown-item"
        :disabled="isUpdating"
        @click="selectStatus(option.value)"
      >
        <span class="d-inline-flex align-items-center gap-2" :class="option.textClass">
          <span>{{ option.emoji }}</span>
          <span>{{ $t(option.labelKey) }}</span>
        </span>
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { getServiceOrderStatusMeta, getServiceOrderStatusOptions } from '@/views/book-types/service/serviceOrderStatus'

const props = defineProps({
  isDisabled: {
    type: Boolean,
    default: false,
  },
  isUpdating: {
    type: Boolean,
    default: false,
  },
  orderStatus: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['change-status'])
const rootRef = ref(null)
const isOpen = ref(false)
const pendingStatus = ref('')

const statusMeta = computed(() => getServiceOrderStatusMeta(props.orderStatus))
const statusOptions = computed(() => getServiceOrderStatusOptions(props.orderStatus))

watch(
  () => props.isUpdating,
  (isUpdating) => {
    if (isUpdating || pendingStatus.value === '') {
      return
    }

    if (props.orderStatus === pendingStatus.value) {
      closeMenu()
    }

    pendingStatus.value = ''
  }
)

function toggleMenu() {
  if (props.isDisabled || props.isUpdating) {
    return
  }

  isOpen.value = !isOpen.value
}

function closeMenu() {
  isOpen.value = false
}

function selectStatus(nextStatus) {
  if (props.isUpdating || props.isDisabled || nextStatus === props.orderStatus) {
    return
  }

  pendingStatus.value = nextStatus
  emit('change-status', nextStatus)
}

function handleDocumentPointerDown(event) {
  if (!isOpen.value || props.isUpdating) {
    return
  }

  const rootElement = rootRef.value

  if (rootElement && !rootElement.contains(event.target)) {
    closeMenu()
  }
}

function handleDocumentKeyDown(event) {
  if (event.key === 'Escape' && isOpen.value && !props.isUpdating) {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handleDocumentPointerDown)
  document.addEventListener('keydown', handleDocumentKeyDown)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleDocumentPointerDown)
  document.removeEventListener('keydown', handleDocumentKeyDown)
})
</script>
