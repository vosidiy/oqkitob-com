<template>


<header class="py-3 bg-base border-bottom">
	<div class="container">
		<div class="d-flex gap-2 align-items-center justify-content-between">
			
			<a href="/" class="hover:opacity-80 d-flex text-decoration-none align-items-center m-0">
				<img src="/assets/img/logo.svg" alt="" height="36">
				<div style="font-size:24px; letter-spacing:1px" class="font-semibold ml-1">Oq<span class="text-secondary">kitob</span> </div>
			</a>

			<div class="nav">
          <a class="nav-link" href="/">Asosiy</a>
          <a class="nav-link" href="/about.html">Haqimizda</a>
          <a class="nav-link" href="/contact.html">Yordam</a>
          <a href="/login"  class="nav-link "> Kirish </a>
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
</script>
