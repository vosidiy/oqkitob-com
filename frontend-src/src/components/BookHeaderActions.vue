<template>
  <div class="relative">
    <button type="button" class="btn btn-default px-2 gap-1" @click="openBookSettingsDialog(props.book)">
      {{ $t('common.actions.settings') }}
    </button>
  </div>
</template>

<script setup>
import { inject } from 'vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const openBookSettingsDialog = inject('openBookSettingsDialog', () => {})
</script>
