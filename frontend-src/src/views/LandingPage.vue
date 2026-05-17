<template>
  <div class="min-h-100vh d-flex flex-column bg-base text-dark">
    <header class="border-bottom bg-white">
      <nav class="container d-flex justify-content-between align-items-center flex-wrap gap-3 py-4">
        <RouterLink class="font-semibold text-decoration-none text-dark" :to="{ name: 'landing' }">
          Oqkitob
        </RouterLink>
        <div class="d-flex align-items-center flex-wrap gap-4">
          <a class="nav-link" href="/about.html">About</a>
          <a class="nav-link" href="/contact.html">Contact</a>
          <a class="nav-link" href="/terms.html">Terms</a>
          <RouterLink class="btn btn-primary" :to="{ name: 'login' }">
            Login
          </RouterLink>
        </div>
      </nav>
    </header>

    <main class="flex-grow d-flex align-items-center py-5">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col-7">
            <span class="text-uppercase text-secondary fw-semibold small">Welcome to Oqkitob</span>
            <h1 class="text-5xl font-bold mt-3 mb-3">Organize your notes, tasks, and ideas in one place.</h1>
            <p class="text-lg text-secondary mb-4">
              Oqkitob helps you keep your workspaces simple and focused, with room to grow into notes,
              tasks, and finance tools over time.
            </p>
            <div class="d-flex flex-wrap gap-3">
              <RouterLink class="btn btn-primary btn-lg" :to="{ name: 'login' }">
                Go to login
              </RouterLink>
              <a class="btn btn-outline btn-lg" href="/about.html">
                Learn more
              </a>
            </div>
          </div>

          <div class="col-4">
            <div class="card shadow-sm">
              <div class="card-body p-4">
                <h2 class="text-3xl font-bold mb-3">Built for focused work</h2>
                <div class="d-grid gap-3">
                  <div class="border rounded p-3 bg-secondary">
                    <div class="font-semibold mb-1">Notes books</div>
                    <div class="small text-secondary">Capture ideas and reference content without clutter.</div>
                  </div>
                  <div class="border rounded p-3 bg-secondary">
                    <div class="font-semibold mb-1">Todo books</div>
                    <div class="small text-secondary">Track open work with simple status and priority signals.</div>
                  </div>
                  <div class="border rounded p-3 bg-secondary">
                    <div class="font-semibold mb-1">Finance books</div>
                    <div class="small text-secondary">Review transactions in the same dashboard as your other work.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <footer class="border-top bg-white py-4">
      <div class="container d-flex flex-column justify-content-between gap-2 text-muted">
        <span>&copy; 2026 Oqkitob</span>
        <span>Organized books for everyday work</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authStore } from '@/stores/auth'

const router = useRouter()

function redirectToHomeIfAuthenticated(checked, user) {
  if (checked && user) {
    router.replace({ name: 'dashboard-home' })
  }
}

watch(
  () => [authStore.state.checked, authStore.state.user],
  ([checked, user]) => {
    // The public landing page should disappear as soon as restored auth state
    // confirms that the user already has a valid session.
    redirectToHomeIfAuthenticated(checked, user)
  },
  { immediate: true }
)
</script>
