<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
      <h1 class="h3 fw-bold mb-2">Forgot password</h1>
      <p class="text-secondary mb-4">Password recovery is not available in this MVP yet.</p>

      <div class="alert alert-secondary mb-4" role="alert">
        This page is scaffolded for the future flow, but there is no backend reset endpoint yet.
      </div>

      <RouterLink class="btn btn-outline-dark" :to="{ name: 'login' }">
        Back to login
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
