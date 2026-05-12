<template>
  <div>
    <h3 class="h6 mb-3">Tasks</h3>

    <div v-if="items.length === 0" class="text-secondary">No tasks yet.</div>

    <div v-else class="list-group">
      <div v-for="item in items" :key="item.id" class="list-group-item">
        <div class="d-flex justify-content-between align-items-start gap-3">
          <div>
            <div class="fw-semibold">{{ item.title }}</div>
            <div v-if="item.description" class="small text-secondary mt-1">{{ item.description }}</div>
          </div>
          <span class="badge" :class="item.is_completed ? 'text-bg-success' : 'text-bg-warning'">
            {{ item.is_completed ? 'Done' : 'Open' }}
          </span>
        </div>
        <div class="small text-secondary mt-2 text-capitalize">
          Priority: {{ item.priority || 'medium' }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>
