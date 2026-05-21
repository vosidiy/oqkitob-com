<template>
<div class="container" style="max-width: 450px;">

  <article class="card shadow-lg p-6 mb-8">

      <h1 class="text-3xl font-bold mb-2">{{ $t('auth.forgotPassword.title') }}</h1>
      <p class="text-secondary mb-4">{{ $t('auth.forgotPassword.subtitle') }}</p>

      <div class="alert alert-info mb-4" role="alert">
       {{ $t('auth.forgotPassword.info') }}
      </div>

      <RouterLink class="btn btn-outline" :to="{ name: 'login' }">
        {{ $t('auth.forgotPassword.backToLogin') }}
      </RouterLink>


  </article>
</div> <!-- container .//end -->


</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>
