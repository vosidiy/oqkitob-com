<template>

<h1 class="text-4xl">404</h1>
<h2>{{ $t('notFound.title') }}</h2>
<p>{{ $t('notFound.message') }}</p>

<div>
  <!-- Sends them to /home (which your router guard handles safely) -->
  <router-link to="/home" class="btn btn-primary">
    {{ $t('notFound.goHome') }}
  </router-link>
  
  <!-- Simple browser back button -->
  <button @click="$router.back()" class="btn btn-secondary">
    {{ $t('notFound.goBack') }}
  </button>
</div>

</template>
