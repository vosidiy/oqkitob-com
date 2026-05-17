<template>
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h1 class="text-3xl font-bold mb-2">Forgot password</h1>
      <p class="text-secondary mb-4">Password recovery is not available in this MVP yet.</p>

      <div class="alert alert-info mb-4" role="alert">
        This page is scaffolded for the future flow, but there is no backend reset endpoint yet.
      </div>

      <RouterLink class="btn btn-outline" :to="{ name: 'login' }">
        Back to login
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
