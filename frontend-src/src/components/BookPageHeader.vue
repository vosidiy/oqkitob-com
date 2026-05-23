<template>
  <header class="d-flex h-14 mobile:h-auto mobile:py-2 shadow-sm border-bottom border-color-neutral-300 flex-shrink-0 bg-base px-5 align-items-center gap-1">
    <div class="min-w-50 mr-auto mobile:min-w-auto mobile:flex-grow mobile:mb-2">
      <h5 class="text-xl text-capitalize">{{ book.title }}</h5>
      <p class="text-secondary text-sm">
        {{ $t('bookHeader.subtitle', { type: $t('bookTypes.' + book.type_key) }) }}
        <span v-if="book.description"> | {{ book.description }}</span>
      </p>
    </div>

    <nav v-if="$slots.nav" class="nav-tabs font-medium mx-auto mobile:order-4 mobile:w-full mobile:flex-nowrap">
      <slot name="nav" />
    </nav>

    <div class="d-flex ml-auto justify-content-end gap-1 min-w-50 mobile:min-w-auto mobile:mb-2">
      <BookHeaderActions :book="book" />
      <slot v-if="$slots.actions" name="actions" />
    </div>
  </header>
</template>

<script setup>
import BookHeaderActions from '@/components/BookHeaderActions.vue'

defineProps({
  book: {
    type: Object,
    required: true,
  },
})
</script>
