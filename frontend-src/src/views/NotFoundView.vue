<template>
  <div class="not-found-container">
    <div class="content">
      <h1 class="error-code">404</h1>
      <h2>Page Not Found</h2>
      <p>Sorry, the page you are looking for doesn't exist or has been moved.</p>
      
      <div class="actions">
        <!-- Sends them to /home (which your router guard handles safely) -->
        <router-link to="/home" class="btn btn-primary">
          Go to Home
        </router-link>
        
        <!-- Simple browser back button -->
        <button @click="$router.back()" class="btn btn-secondary">
          Go Back
        </button>
      </div>
    </div>
  </div>
</template>
