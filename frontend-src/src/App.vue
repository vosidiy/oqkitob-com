<template>
  <div class="min-vh-100 d-flex flex-column bg-light text-dark">
    <header class="border-bottom bg-white">
      <nav class="container navbar navbar-expand-lg py-3">
        <a class="navbar-brand fw-semibold" href="/">Oqkitob</a>
        <div class="navbar-nav ms-auto gap-lg-3">
          <a class="nav-link" href="/about.html">About</a>
          <a class="nav-link" href="/contact.html">Contact</a>
          <a class="nav-link" href="/terms.html">Terms</a>
        </div>
      </nav>
    </header>

    <main class="flex-grow-1 d-flex align-items-center py-5">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="text-uppercase text-secondary fw-semibold small">Welcome to Oqkitob</span>
            <h1 class="display-4 fw-bold mt-3 mb-3">Organize your notes, tasks, and ideas in one place.</h1>
            <p class="lead text-secondary mb-0">
              Oqkitob helps you keep your workspaces simple and focused, with room to grow into notes,
              tasks, and finance tools over time.
            </p>
          </div>

          <div class="col-lg-5 ms-lg-auto">
            <div class="card border-0 shadow-sm">
              <div class="card-body p-4 p-md-5">
                <h2 class="h3 fw-bold mb-2">Login</h2>
                <p class="text-secondary mb-4">Sign in to continue when authentication is connected.</p>

                <form @submit.prevent>
                  <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input id="email" type="email" class="form-control form-control-lg" placeholder="you@example.com">
                  </div>

                  <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" class="form-control form-control-lg" placeholder="Enter your password">
                  </div>

                  <button type="submit" class="btn btn-dark btn-lg w-100">Login</button>
                </form>
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
