<template>

<div class="container py-8">
    <h2 class="text-xl mb-3">User Information</h2>

      <div v-if="user" class="row g-3 mb-4">
        <div class="col-6">
          <div class="text-secondary small">Name</div>
          <div class="fw-semibold">{{ user.name || '-' }}</div>
        </div>
        <div class="col-6">
          <div class="text-secondary small">Email</div>
          <div class="fw-semibold">{{ user.email || '-' }}</div>
        </div>
        <div class="col-6">
          <div class="text-secondary small">City</div>
          <div class="fw-semibold">{{ user.city || '-' }}</div>
        </div>
        <div class="col-6">
          <div class="text-secondary small">Country</div>
          <div class="fw-semibold">{{ user.country_name || '-' }}</div>
        </div>
        <div class="col-6">
          <div class="text-secondary small">Timezone</div>
          <div class="fw-semibold">{{ user.timezone || '-' }}</div>
        </div>
        <div class="col-6">
          <div class="text-secondary small">Plan</div>
          <div class="fw-semibold text-capitalize">{{ user.plan || '-' }}</div>
        </div>
      </div>

      <div class="alert alert-info" role="alert">
        Select any book from right panel
      </div>
</div>

</template>

<script setup>
import { authStore } from '@/stores/auth'

const user = authStore.state.user
</script>
