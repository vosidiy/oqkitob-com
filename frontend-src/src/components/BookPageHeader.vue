<template>
  <header v-if="isMobileAppLayout" class="d-flex flex-col flex-shrink-0 bg-base border-bottom border-color-neutral-300">
    <div class="d-flex align-items-center gap-2 h-14 px-2">
      <RouterLink :to="{ name: 'dashboard-home' }" class="btn btn-icon btn-default flex-shrink-0" :title="$t('common.actions.back')">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-icon lucide-arrow-left"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
      </RouterLink>

      <div class="min-w-0 flex-grow">
        <h5 class="text-base text-capitalize font-semibold" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ book.title }}</h5>
        <p class="text-secondary text-sm" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          {{ $t('bookTypes.' + book.type_key) }}
          <span v-if="book.description"> | {{ book.description }}</span>
        </p>
      </div>
    </div>

    <nav v-if="$slots.nav" class="nav-tabs font-medium w-full overflow-x-auto flex-nowrap px-2">
      <slot name="nav" />
    </nav>
  </header>

  <header v-else class="d-flex h-14 mobile:h-auto mobile:py-2 shadow-sm border-bottom border-color-neutral-300 flex-shrink-0 bg-base px-5 align-items-center gap-1">
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
import { inject } from 'vue'
import { RouterLink } from 'vue-router'
import BookHeaderActions from '@/components/BookHeaderActions.vue'

defineProps({
  book: {
    type: Object,
    required: true,
  },
})

const isMobileAppLayout = inject('isMobileAppLayout', false)
</script>
