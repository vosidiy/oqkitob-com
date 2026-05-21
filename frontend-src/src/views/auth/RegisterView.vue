<template>
<div class="container" style="max-width: 450px;">

  <article class="card shadow-lg p-6 mb-8">
  
    <h4 class="text-2xl font-bold mb-2">{{ $t('auth.register.title') }}</h4>
    <p class="text-secondary mb-4">{{ $t('auth.register.subtitle') }}</p>

    <div class="alert alert-info mb-4" role="alert">
      {{ $t('auth.register.info') }}
    </div>

    <RouterLink class="btn btn-outline" :to="{ name: 'login' }">
      {{ $t('auth.register.backToLogin') }}
    </RouterLink>

  </article>

</div> <!-- container .//end -->

</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
