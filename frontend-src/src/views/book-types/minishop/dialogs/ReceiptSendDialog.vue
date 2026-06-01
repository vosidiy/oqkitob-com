<template>
  <dialog ref="dialogRef" class="dialog-sm mt-10" @cancel="handleCancel" @close="handleClose">
    <header class="dialog-header">
      <h5>{{ $t('minishop.dialogs.receiptSendTitle') }}</h5>
      <button type="button" class="btn btn-icon" :disabled="isSharing" @click="close">
        <svg viewBox="0 0 24 24" width="24" height="24"><path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="2"></path></svg>
      </button>
    </header>

    <div class="dialog-body">

      <a
          class="btn btn-default w-full flex-1"
          :href="publicPdfUrl"
          target="_blank"
          rel="noopener"
          :download="`receipt-${props.saleId}.pdf`"
        >
          {{ $t('minishop.dialogs.downloadReceiptPdf') }}
      </a>

      <p class="text-muted text-center my-2">
        YOKI
      </p>

      <input
        :id="linkInputId"
        ref="linkInputRef"
        type="text"
        class="form-control"
        :value="publicReceiptUrl"
        readonly
        @focus="$event.target.select()"
      >
      <p v-if="feedbackMessage" :class="feedbackTone === 'error' ? 'text-red mt-2' : 'text-green mt-2'">
        {{ feedbackMessage }}
      </p>

      <div class="d-flex gap-2 mt-4 mobile:flex-col">
        <button
          type="button"
          class="btn btn-primary flex-1"
          :disabled="!canShareReceipt || isSharing"
          @click="handleCopyAndSend"
        >
          <span v-if="isSharing">{{ $t('common.states.loading') }}</span>
          <span v-else>{{ $t('minishop.dialogs.copyAndSendReceiptLink') }}</span>
        </button>
      </div>

      <div class="border-top pt-3 mt-4">
        <p class="text-secondary text-sm mb-2">{{ $t('minishop.dialogs.receiptShareFallbackHint') }}</p>
        <div class="d-flex gap-2">
          <a class="btn flex-1 btn-neutral" :href="whatsAppUrl" target="_blank" rel="noopener">
            {{ $t('minishop.dialogs.shareViaWhatsApp') }}
          </a>
          <a class="btn flex-1 btn-neutral" :href="telegramUrl" target="_blank" rel="noopener">
            {{ $t('minishop.dialogs.shareViaTelegram') }}
          </a>
        </div>
      </div>
    </div>
  </dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  bookId: {
    type: String,
    default: '',
  },
  saleId: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['cancel', 'close'])
const { t } = useI18n()
const dialogRef = ref(null)
const linkInputRef = ref(null)
const isSharing = ref(false)
const feedbackMessage = ref('')
const feedbackTone = ref('success')
const linkInputId = computed(() => {
  const rawId = `receipt-public-link-${props.bookId || 'book'}-${props.saleId || 'sale'}`

  return rawId.replaceAll(/[^a-zA-Z0-9_-]/g, '-')
})

const publicReceiptUrl = computed(() => {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''

  if (origin === '' || props.bookId === '' || props.saleId === '') {
    return ''
  }

  return `${origin}/api/public/books/${encodeURIComponent(props.bookId)}/minishop/sales/${encodeURIComponent(props.saleId)}/receipt`
})

const publicPdfUrl = computed(() => {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''

  if (origin === '' || props.bookId === '' || props.saleId === '') {
    return ''
  }

  return `${origin}/api/public/books/${encodeURIComponent(props.bookId)}/minishop/sales/${encodeURIComponent(props.saleId)}/receipt.pdf`
})

const whatsAppUrl = computed(() => {
  return `https://wa.me/?text=${encodeURIComponent(publicReceiptUrl.value)}`
})

const telegramUrl = computed(() => {
  return `https://t.me/share/url?url=${encodeURIComponent(publicReceiptUrl.value)}`
})

const canShareReceipt = computed(() => {
  return publicReceiptUrl.value !== '' && publicPdfUrl.value !== ''
})

function open() {
  if (!canShareReceipt.value || dialogRef.value?.open) {
    return
  }

  feedbackMessage.value = ''
  feedbackTone.value = 'success'
  dialogRef.value?.showModal()
}

function close() {
  if (dialogRef.value?.open) {
    dialogRef.value.close()
  }
}

function isOpen() {
  return dialogRef.value?.open === true
}

function handleCancel(event) {
  if (isSharing.value) {
    event.preventDefault()
    return
  }

  emit('cancel', event)
}

function handleClose() {
  isSharing.value = false
  feedbackMessage.value = ''
  feedbackTone.value = 'success'
  emit('close')
}

async function handleCopyAndSend() {
  if (!canShareReceipt.value || isSharing.value) {
    return
  }

  isSharing.value = true
  feedbackMessage.value = ''
  feedbackTone.value = 'success'

  const copied = await tryCopyText(publicReceiptUrl.value)

  try {
    const sharedPdf = await trySharePdfFile()

    if (sharedPdf) {
      feedbackMessage.value = copied
        ? t('minishop.dialogs.receiptLinkCopied')
        : t('minishop.dialogs.receiptShared')
      return
    }

    const sharedUrl = await tryShareUrl()

    if (sharedUrl) {
      feedbackMessage.value = copied
        ? t('minishop.dialogs.receiptLinkCopied')
        : t('minishop.dialogs.receiptShared')
      return
    }

    if (copied) {
      feedbackMessage.value = t('minishop.dialogs.receiptLinkCopiedFallback')
      return
    }

    feedbackTone.value = 'error'
    feedbackMessage.value = t('minishop.dialogs.unableCopyOrShareReceipt')
  } catch (error) {
    if (error instanceof Error && error.name === 'AbortError') {
      feedbackMessage.value = copied
        ? t('minishop.dialogs.receiptLinkCopiedFallback')
        : ''
      return
    }

    feedbackTone.value = copied ? 'success' : 'error'
    feedbackMessage.value = copied
      ? t('minishop.dialogs.receiptLinkCopiedFallback')
      : t('minishop.dialogs.unableCopyOrShareReceipt')
  } finally {
    isSharing.value = false
  }
}

async function trySharePdfFile() {
  if (
    typeof window === 'undefined'
    || typeof navigator === 'undefined'
    || typeof navigator.share !== 'function'
  ) {
    return false
  }

  const response = await window.fetch(publicPdfUrl.value)

  if (!response.ok) {
    throw new Error(`Unable to fetch receipt PDF: ${response.status}`)
  }

  const blob = await response.blob()
  const file = new File([blob], `receipt-${props.saleId}.pdf`, {
    type: 'application/pdf',
  })

  if (typeof navigator.canShare !== 'function') {
    return false
  }

  if (!navigator.canShare({ files: [file] })) {
    return false
  }

  await navigator.share({
    title: 'Receipt',
    files: [file],
  })

  return true
}

async function tryShareUrl() {
  if (typeof navigator === 'undefined' || typeof navigator.share !== 'function') {
    return false
  }

  await navigator.share({
    title: 'Receipt',
    url: publicReceiptUrl.value,
  })

  return true
}

async function tryCopyText(text) {
  if (text === '') {
    return false
  }

  if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(text)
      return true
    } catch (error) {
      // Fall through to the manual copy fallback below.
    }
  }

  const input = linkInputRef.value

  if (!input || typeof document === 'undefined' || typeof document.execCommand !== 'function') {
    return false
  }

  input.focus()
  input.select()

  try {
    return document.execCommand('copy')
  } catch (error) {
    return false
  }
}

defineExpose({
  close,
  isOpen,
  open,
})
</script>
