import { onBeforeUnmount, ref } from 'vue'

const DEFAULT_TOAST_DURATION = 2000

export function useToast(defaultDuration = DEFAULT_TOAST_DURATION) {
  const feedbackMessage = ref('')
  let toastTimer = null

  function clearTimer() {
    if (toastTimer !== null) {
      window.clearTimeout(toastTimer)
      toastTimer = null
    }
  }

  function clearToast() {
    clearTimer()
    feedbackMessage.value = ''
  }

  function showToast(message, duration = defaultDuration) {
    clearTimer()
    feedbackMessage.value = String(message ?? '').trim()

    if (feedbackMessage.value === '') {
      return
    }

    toastTimer = window.setTimeout(() => {
      feedbackMessage.value = ''
      toastTimer = null
    }, duration)
  }

  onBeforeUnmount(() => {
    clearTimer()
  })

  return {
    clearToast,
    feedbackMessage,
    showToast,
  }
}
