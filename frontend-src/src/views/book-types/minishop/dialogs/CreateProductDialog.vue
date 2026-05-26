<template>
  <dialog ref="dialogRef" class="mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('minishop.dialogs.createProduct') }}</h5>
      <button class="btn btn-icon" :disabled="isCreatingProduct" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>
    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          {{ errorMessage }}
        </div>

        <div class="mb-4">
          <label class="form-label" for="create-product-name">{{ $t('common.fields.name') }}</label>
          <input
            id="create-product-name"
            v-model.trim="form.name"
            type="text"
            class="form-control"
            :placeholder="$t('minishop.dialogs.enterProductName')"
            :disabled="isCreatingProduct"
            required
          >
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="create-product-category">{{ $t('common.fields.category') }}</label>
            <select
              id="create-product-category"
              v-model="form.category_id"
              class="form-select"
              :disabled="isCreatingProduct || isLoadingCategories"
            >
              <option value="">--- {{ $t('minishop.main.noCategory') }} ---</option>
              <option :value="createCategoryOptionValue">+ {{ $t('minishop.dialogs.addCategory') }}</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <input
              v-if="isNewCategorySelected"
              id="create-product-new-category"
              v-model.trim="form.new_category_name"
              type="text"
              class="form-control mt-3"
              :placeholder="$t('minishop.dialogs.enterCategoryName')"
              :disabled="isCreatingProduct"
            >
          </div>
          <div class="col-6 mb-4">
            <label class="form-label" for="create-product-sku">{{ $t('minishop.dialogs.productCode') }}</label>
            <input
              id="create-product-sku"
              v-model.trim="form.sku"
              type="text"
              class="form-control"
              :placeholder="$t('minishop.dialogs.optionalSku')"
              :disabled="isCreatingProduct"
            >
          </div>
        </div>

        <div class="row gap-3">
          <div class="col-6 mb-4">
            <label class="form-label" for="create-product-price">{{ $t('common.fields.price') }}</label>
            <input
              id="create-product-price"
              v-model.trim="form.price"
              type="number"
              class="form-control"
              min="0"
              step="0.01"
              placeholder="0.00"
              :disabled="isCreatingProduct"
              required
            >
            <p class="small text-secondary mt-1">
              <small class="currency-code">{{ props.book.currency_code }}</small>
            </p>
          </div>
          <div class="col-3 mb-4">
            <label class="form-label" for="create-product-quantity">{{ $t('common.fields.quantity') }}</label>
            <input
              id="create-product-quantity"
              v-model.trim="form.quantity"
              type="number"
              class="form-control"
              min="0"
              step="0.001"
              placeholder="0"
              :disabled="isCreatingProduct"
              required
            >
          </div>
          <div class="col-3 mb-4">
            <label class="form-label" for="create-product-low-stock-alert">{{ $t('minishop.dialogs.lowStockAlert') }}</label>
            <input
              id="create-product-low-stock-alert"
              v-model.trim="form.low_stock_alert"
              type="number"
              class="form-control"
              min="0"
              step="0.001"
              :placeholder="$t('minishop.dialogs.optionalThreshold')"
              :disabled="isCreatingProduct"
            >
          </div>
        </div>

        <div class="pt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary" :disabled="isSubmitDisabled">
            <span v-if="isCreatingProduct">{{ $t('common.states.saving') }}</span>
            <span v-else>{{ $t('common.actions.create') }}</span>
          </button>
          <button type="button" class="btn btn-default" :disabled="isCreatingProduct" @click="close">
            {{ $t('common.actions.cancel') }}
          </button>
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  book: {
    type: Object,
    required: true,
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
  isCreatingProduct: {
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
})

const emit = defineEmits(['cancel', 'close', 'submit'])
const dialogRef = ref(null)

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
