<template>
  <div id="app" style="font-family: sans-serif; padding: 2rem;">
    <h1>Vue + CI4 Connectivity Test</h1>
    
    <div v-if="loading">Checking connection...</div>
    
    <div v-else-if="error" style="color: red;">
      <strong>Error:</strong> {{ error }}
    </div>

    <div v-else style="background: #f0fdf4; border: 1px solid #16a34a; padding: 1rem; border-radius: 8px;">
      <h3 style="color: #16a34a;">✅ {{ apiData.status }}</h3>
      <p>{{ apiData.message }}</p>
      <p><small>Server Time: {{ apiData.date }}</small></p>
      
      <h4>Tech Stack:</h4>
      <ul>
        <li v-for="item in apiData.stack" :key="item">{{ item }}</li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const apiData = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    // Vite proxy forwards '/api/test' to 'http://localhost:8888/api/test'
    const response = await axios.get('/api/test');
    apiData.value = response.data;
  } catch (err) {
    error.value = err.message || "Could not reach the API";
    console.error("API Error:", err);
  } finally {
    loading.value = false;
  }
});
</script>


<style scoped></style>
