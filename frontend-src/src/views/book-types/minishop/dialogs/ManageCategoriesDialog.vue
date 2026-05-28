<template>
  <dialog ref="dialogRef" class="dialog-md mt-10" @cancel="emit('cancel', $event)" @close="emit('close')">
    <header class="dialog-header">
      <h5>{{ $t('minishop.dialogs.manageCategories') }}</h5>
      <button class="btn btn-icon" :disabled="isSaving" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>

    <div class="dialog-body">
      <form @submit.prevent="emit('submit')">
        <div v-if="errorMessage" class="alert alert-danger mb-3" role="alert">
          {{ errorMessage }}
        </div>



        <div v-else class="table-responsive mb-3">
          <table class="table">
            <thead>
              <tr>
                <th>{{ $t('common.fields.name') }}</th>
                <th class="text-center">{{ $t('minishop.dialogs.products') }}</th>
                <th class="text-center">{{ $t('common.actions.delete') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="rows.length === 0"> 
                 <td colspan="2"> <p class="text-secondary text-lg"> {{ $t('minishop.dialogs.noCategoriesYet') }} </p></td>
                 
              </tr>
              <tr v-for="(row, index) in rows" :key="row.key">
                <td width="80%">
                  <input
                    :id="`manage-category-${index}`"
                    v-model.trim="row.name"
                    type="text"
                    class="form-control"
                    :placeholder="$t('minishop.dialogs.enterCategoryName')"
                    :disabled="isSaving"
                  >
                </td>
                <td class="text-center align-middle text-secondary">
                  {{ row.product_count }}
                </td>
                <td class="text-center align-middle">
                  <button
                    v-if="row.is_new"
                    type="button"
                    class="btn btn-sm btn-plain text-red"
                    :disabled="isSaving"
                    @click="emit('remove-row', row.key)"
                  >
                    {{ $t('common.actions.delete') }}
                  </button>
                  <label v-else class="d-inline-flex text-red  align-items-center gap-2">
                    <input v-model="row.remove" type="checkbox" :disabled="isSaving">
                    <span>{{ $t('common.actions.delete') }}</span>
                  </label>
                </td>
              </tr>
              <tr>
                <td>
                  <button type="button" class="btn w-full btn-neutral" :disabled="isSaving" @click="emit('add-row')">
                    + {{ $t('minishop.dialogs.addAnotherCategory') }}
                  </button>
                </td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn flex-grow btn-primary" :disabled="isSubmitDisabled">
              <span v-if="isSaving">{{ $t('common.states.saving') }}</span>
              <span v-else>{{ $t('common.actions.saveChanges') }}</span>
            </button>
            <button type="button" class="btn btn-default" :disabled="isSaving" @click="close">
              {{ $t('common.actions.cancel') }}
            </button>
            
        </div>
      </form>
    </div>
  </dialog>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  errorMessage: {
    type: String,
    default: '',
  },
  isSaving: {
    type: Boolean,
    default: false,
  },
  isSubmitDisabled: {
    type: Boolean,
    default: false,
  },
  rows: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['add-row', 'cancel', 'close', 'remove-row', 'submit'])
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
