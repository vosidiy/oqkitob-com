<template>
<div class="container" style="max-width: 450px;">

  <article class="card shadow-lg p-6 mb-8">

      <h1 class="text-3xl font-bold mb-2">Forgot password</h1>
      <p class="text-secondary mb-4">Password recovery is not available in this MVP yet.</p>

      <div class="alert alert-info mb-4" role="alert">
       Contact support to restore account
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
