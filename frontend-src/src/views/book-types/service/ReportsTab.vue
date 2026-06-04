<template>
  <main class="p-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="h5 mb-2">{{ $t('service.reports.title') }}</h3>
        <p class="text-secondary mb-0">{{ $t('service.reports.placeholder') }}</p>
      </div>
    </div>
  </main>
</template>

<script setup>
defineProps({
  book: {
    type: Object,
    required: true,
  },
})
</script>
