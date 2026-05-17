import { ref } from 'vue'

const activeBook = ref(null)
const isOpen = ref(false)

export function useBookSettingsDialog() {
  function openBookSettingsDialog(book) {
    activeBook.value = book ?? null
    isOpen.value = true
  }

  function closeBookSettingsDialog() {
    isOpen.value = false
  }

  function clearBookSettingsDialog() {
    isOpen.value = false
    activeBook.value = null
  }

  return {
    activeBook,
    isOpen,
    openBookSettingsDialog,
    closeBookSettingsDialog,
    clearBookSettingsDialog,
  }
}
