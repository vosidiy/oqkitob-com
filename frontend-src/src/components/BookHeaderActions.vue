<template>
  <div class="relative">
    <button type="button" class="btn btn-default px-2 gap-1" @click="openDialog">
      {{ $t('common.actions.settings') }}
    </button>
  </div>
</template>

<script setup>
import { useBookSettingsDialog } from '@/composables/book-settings-dialog'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const { openBookSettingsDialog } = useBookSettingsDialog()

function openDialog() {
  openBookSettingsDialog(props.book)
}
</script>
