<template>
  <div class="min-vh-100 d-flex flex-column bg-light text-dark">
    <header class="border-bottom bg-white">
      <nav class="container navbar navbar-expand-lg py-3">
        <RouterLink class="navbar-brand fw-semibold" :to="{ name: 'landing' }">
          Oqkitob
        </RouterLink>
        <div class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
          <a class="nav-link" href="/about.html">About</a>
          <a class="nav-link" href="/contact.html">Contact</a>
          <a class="nav-link" href="/terms.html">Terms</a>
          <RouterLink class="btn btn-dark ms-lg-2" :to="{ name: 'login' }">
            Login
          </RouterLink>
        </div>
      </nav>
    </header>

    <main class="flex-grow-1 d-flex align-items-center py-5">
      <div class="container">
        <div class="row align-items-center g-5 justify-content-between">
          <div class="col-lg-7">
            <span class="text-uppercase text-secondary fw-semibold small">Welcome to Oqkitob</span>
            <h1 class="display-4 fw-bold mt-3 mb-3">Organize your notes, tasks, and ideas in one place.</h1>
            <p class="lead text-secondary mb-4">
              Oqkitob helps you keep your workspaces simple and focused, with room to grow into notes,
              tasks, and finance tools over time.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3">
              <RouterLink class="btn btn-dark btn-lg" :to="{ name: 'login' }">
                Go to login
              </RouterLink>
              <a class="btn btn-outline-dark btn-lg" href="/about.html">
                Learn more
              </a>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-4 p-md-5">
                <h2 class="h3 fw-bold mb-3">Built for focused work</h2>
                <div class="d-grid gap-3">
                  <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold mb-1">Notes books</div>
                    <div class="small text-secondary">Capture ideas and reference content without clutter.</div>
                  </div>
                  <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold mb-1">Todo books</div>
                    <div class="small text-secondary">Track open work with simple status and priority signals.</div>
                  </div>
                  <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold mb-1">Finance books</div>
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
      <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
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
