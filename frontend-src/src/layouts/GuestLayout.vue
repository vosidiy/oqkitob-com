<template>
  <div class="min-vh-100 bg-light">
    <header class="border-bottom bg-white">
      <nav class="container navbar navbar-expand-lg py-3">
        <RouterLink class="navbar-brand fw-semibold" :to="{ name: 'landing' }">
          Oqkitob
        </RouterLink>
        <div class="navbar-nav ms-auto gap-lg-3">
          <a class="nav-link" href="/about.html">About</a>
          <a class="nav-link" href="/contact.html">Contact</a>
          <a class="nav-link" href="/terms.html">Terms</a>
        </div>
      </nav>
    </header>

    <main class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-6 col-xl-5">
          <RouterView />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>
