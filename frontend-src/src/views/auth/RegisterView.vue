<template>
<div class="container" style="max-width: 450px;">

  <article class="card shadow-lg p-6 mb-8">
  
    <h4 class="text-2xl font-bold mb-2">Register</h4>
    <p class="text-secondary mb-4">Account creation is not available in this MVP yet.</p>

    <div class="alert alert-info mb-4" role="alert">
      Registration UI is scaffolded, but the backend endpoint is not implemented yet.
    </div>

    <RouterLink class="btn btn-outline" :to="{ name: 'login' }">
      Back to login
    </RouterLink>

  </article>

</div> <!-- container .//end -->

</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
