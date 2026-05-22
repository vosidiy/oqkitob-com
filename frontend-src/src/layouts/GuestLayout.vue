<template>
<header class="py-3 bg-base border-bottom">
	<div class="container">
		<div class="d-flex gap-2 align-items-center justify-content-between">
			
			<a href="/" class="hover:opacity-80 d-flex text-decoration-none align-items-center m-0">
				<img src="/assets/img/logo.svg" alt="" height="36">
				<div style="font-size:24px; letter-spacing:1px" class="font-semibold ml-1">Oq<span class="text-secondary">kitob</span> </div>
			</a>

			<div class="d-flex align-items-center gap-3">
        <div class="nav">
          <a class="nav-link" href="/">{{ $t('nav.home') }}</a>
          <a class="nav-link" href="/about.html">{{ $t('nav.about') }}</a>
          <a class="nav-link" href="/contact.html">{{ $t('nav.help') }}</a>
          <a href="/login" class="nav-link">{{ $t('nav.login') }}</a>
			  </div>
        <select
          id="guest_language_picker"
          v-model="currentLocale"
          :aria-label="$t('guestLayout.ariaLanguagePicker')"
          class="form-select min-w-10 w-25"
        >
          <option v-for="option in localeOptions" :key="option.code" :value="option.code">
            {{ option.label }}
          </option>
        </select>
      </div>

		</div>
	</div> <!-- container end.// -->
</header>

<section class="py-20 bg-lower min-h-90vh bg-lower rectangle-pattern">
   <RouterView />
</section>




</template>

<script setup>
import { RouterView } from 'vue-router'
import { useLocale } from '@/composables/use-locale'

const { currentLocale, localeOptions } = useLocale()
</script>
