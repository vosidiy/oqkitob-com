<template>
  <div class="min-h-100vh bg-base">
    <header class="border-bottom bg-white">
      <nav class="container d-flex justify-content-between align-items-center flex-wrap gap-3 py-4">
        <a class="font-semibold text-decoration-none text-dark" href="/">
          Oqkitob
        </a>
        <div class="d-flex align-items-center flex-wrap gap-4">
          <a class="nav-link" href="/about.html">About</a>
          <a class="nav-link" href="/contact.html">Contact</a>
          <a class="nav-link" href="/terms.html">Terms</a>
        </div>
      </nav>
    </header>

    <main class="container py-5">
      <div class="row justify-content-center">
        <div class="col-12">
          <RouterView />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router'
</script>
