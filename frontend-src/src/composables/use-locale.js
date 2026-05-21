import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  FALLBACK_LOCALE,
  getCurrentLocale,
  isSupportedLocale,
  setLocale,
  SUPPORTED_LOCALES,
} from '@/i18n'

const languageLabelsByCode = {
  en: '🇺🇸 Eng',
  ru: '🇷🇺 Рус',
  uz: "🇺🇿 Uzb",
}

export function useLocale() {
  const { locale } = useI18n()

  const currentLocale = computed({
    get() {
      return locale.value
    },
    set(nextLocale) {
      locale.value = setLocale(nextLocale)
    },
  })

  const localeOptions = computed(() => {
    return SUPPORTED_LOCALES.map((code) => ({
      code,
      label: languageLabelsByCode[code] ?? code,
    }))
  })

  function changeLocale(nextLocale) {
    currentLocale.value = nextLocale
    return currentLocale.value
  }

  return {
    currentLocale,
    changeLocale,
    fallbackLocale: FALLBACK_LOCALE,
    getCurrentLocale,
    isSupportedLocale,
    localeOptions,
  }
}
