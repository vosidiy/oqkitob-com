<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('minishop.dialogs.editProduct') }}</h5>
      <button class="btn btn-icon" :disabled="isBusy" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>
    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <div class="mb-4">
          <label class="form-label" for="edit-product-name">{{ $t('common.fields.name') }}</label>
          <input
            id="edit-product-name"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :placeholder="$t('minishop.dialogs.enterProductName')"
            :disabled="isBusy"
            required
          >
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="edit-product-category">{{ $t('common.fields.category') }}  <a href="#" @click.prevent="emit('open-manage-categories')">[⚙️]</a> </label>
            <select
              id="edit-product-category"
              v-model="form.category_id"
              class="form-select"
              :disabled="isBusy || isLoadingCategories"
            >
              <option value=""> --- {{ $t('minishop.main.noCategory') }} --- </option>
              <option :value="createCategoryOptionValue">+ {{ $t('minishop.dialogs.addCategory') }}</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <input
              v-if="isNewCategorySelected"
              id="edit-product-new-category"
              v-model.trim="form.new_category_name"
              type="text"
              class="form-control mt-3"
              :placeholder="$t('minishop.dialogs.enterCategoryName')"
              :disabled="isBusy"
            >
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="edit-product-sku">{{ $t('minishop.dialogs.productCode') }}</label>
            <input
              id="edit-product-sku"
              v-model.trim="form.sku"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.dialogs.optionalSku')"
              :disabled="isBusy"
            >
          </div>
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            
            <label class="form-label" for="edit-product-price">{{ $t('common.fields.price') }}</label>
            <div class="relative">
              <input
                id="edit-product-price"
                v-model.trim="form.price"
                type="number"
                class="form-control"
                min="0"
                step="0.01"
                placeholder="0.00"
                :disabled="isBusy"
                required
              >
              <small class="currency-code text-secondary text-default  bg-neutral-50 absolute right-2 top-1 p-1">{{ props.book.currency_code }}</small>
            </div>
          </div>
          <div class="col-3 mb-4">
            <label class="form-label" for="edit-product-quantity">{{ $t('common.fields.quantity') }}</label>
            <input
              id="edit-product-quantity"
              v-model.trim="form.quantity"
              type="number"
              class="form-control"
              min="0"
              step="0.001"
              placeholder="0"
              :disabled="isBusy"
              required
            >
          </div>
          <div class="col-3 mb-4">
            <label class="form-label" for="edit-product-low-stock-alert">{{ $t('minishop.dialogs.lowStockAlert') }}</label>
            <input
              id="edit-product-low-stock-alert"
              v-model.trim="form.low_stock_alert"
              type="number"
              class="form-control"
              min="0"
              step="0.001"
              :placeholder="$t('minishop.dialogs.optionalThreshold')"
              :disabled="isBusy"
            >
          </div>
        </div>

        <div class="pt-4 d-flex justify-content-between gap-2">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitDisabled">
            <span v-if="isUpdatingProduct">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('common.actions.saveChanges') }}</span>
          </button>

          <button type="button" class="btn btn-default" :disabled="isBusy" @click="close">
            {{ $t('common.actions.cancel') }}
          </button>

          <button
            type="button"
            class="btn btn-red-subtle ml-auto"
            :disabled="isBusy || !canDeactivate"
            @click="emit('deactivate')"
          >
            <span v-if="isDeactivatingProduct">{{ $t('common.states.updating') }}</span>
            <span v-else>{{ $t('common.actions.deactivate') }}</span>
          </button>
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
  },
  canDeactivate: {
    type: Boolean,
    default: false,
  },
  categories: {
    type: Array,
    default: () => [],
  },
  createCategoryOptionValue: {
    type: String,
    required: true,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  isDeactivatingProduct: {
    type: Boolean,
    default: false,
  },
  isLoadingCategories: {
    type: Boolean,
    default: false,
  },
  isNewCategorySelected: {
    type: Boolean,
    default: false,
  },
  isSubmitDisabled: {
    type: Boolean,
    default: false,
  },
  isUpdatingProduct: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['cancel', 'close', 'deactivate', 'submit'])
const dialogRef = ref(null)
const isBusy = computed(() => props.isUpdatingProduct || props.isDeactivatingProduct)

function open() {
  if (!dialogRef.value?.open) {
    dialogRef.value?.showModal()
  }
}

function close() {
  if (dialogRef.value?.open) {
    dialogRef.value.close()
  }
}

function isOpen() {
  return dialogRef.value?.open === true
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
