<template>

<div class="container py-8">


      
    <p class="text-center text-lg  mt-20 m-10"> 
        <span class="text-muted">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-notebook-icon lucide-notebook"><path d="M2 6h4"></path><path d="M2 10h4"></path><path d="M2 14h4"></path><path d="M2 18h4"></path><rect width="16" height="20" x="4" y="2" rx="2"></rect><path d="M16 2v20"></path></svg>
        </span>
      <br> <br> 
       👈     {{ $t('home.selectBook') }}
    </p>


    <article class="card p-5 mx-auto" style="max-width: 700px; margin-top:20%">
        <p class="text-secondary font-semibold">{{ $t('home.title') }}</p>
        <hr>
        <ul v-if="user" class="d-grid gap-3 grid-template-cols-3 text-secondary">
            <li> {{ $t('common.fields.name') }}: <br> {{ user.name || '-' }} 
            </li>
            <li> {{ $t('common.fields.phone') }}: <br> {{ user.phone || '-' }} 
            </li>
            <li v-if="user.email"> {{ $t('common.fields.email') }}: <br>  {{ user.email }}
            </li>
            <li> {{ $t('common.fields.city') }}: <br> {{ user.city || '-' }}  </li>
            <li>  {{ $t('common.fields.country') }}: <br> {{ user.country_name || '-' }}  </li>
            <li>
              {{ $t('common.fields.timezone') }}: <br> {{ user.timezone || '-' }} 
            </li>
      </ul>

    </article>
    
</div>

</template>

<script setup>
import { authStore } from '@/stores/auth'

const user = authStore.state.user
</script>
